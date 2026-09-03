<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Resizes and stores product images using PHP's GD extension,
 * which ships with XAMPP — no composer package needed.
 *
 * Phone photos are often 4000px and several megabytes. Storing them
 * untouched makes the storefront crawl on mobile data, so everything
 * is capped on the long edge before it hits disk.
 */
class ImageService
{
    private const MAX_EDGE = 1400;
    private const QUALITY  = 82;

    public function store(UploadedFile $file, string $folder = 'products'): string
    {
        $name = Str::uuid() . '.jpg';
        $path = $folder . '/' . $name;

        $image = $this->readAsResource($file);

        if ($image === null) {
            // GD could not read it — fall back to storing the original.
            return $file->store($folder, 'public');
        }

        $resized = $this->resize($image);

        ob_start();
        imagejpeg($resized, null, self::QUALITY);
        $binary = ob_get_clean();

        imagedestroy($image);
        if ($resized !== $image) {
            imagedestroy($resized);
        }

        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function readAsResource(UploadedFile $file)
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $contents = file_get_contents($file->getRealPath());
        $image    = @imagecreatefromstring($contents);

        return $image ?: null;
    }

    private function resize($image)
    {
        $width  = imagesx($image);
        $height = imagesy($image);
        $long   = max($width, $height);

        if ($long <= self::MAX_EDGE) {
            return $this->flatten($image, $width, $height);
        }

        $scale     = self::MAX_EDGE / $long;
        $newWidth  = (int) round($width * $scale);
        $newHeight = (int) round($height * $scale);

        return $this->flatten($image, $newWidth, $newHeight, $width, $height);
    }

    /** Draw onto a white canvas so transparent PNGs do not turn black as JPEG. */
    private function flatten($image, int $w, int $h, ?int $srcW = null, ?int $srcH = null)
    {
        $canvas = imagecreatetruecolor($w, $h);
        $white  = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $w, $h, $white);

        imagecopyresampled(
            $canvas, $image,
            0, 0, 0, 0,
            $w, $h,
            $srcW ?? $w, $srcH ?? $h
        );

        return $canvas;
    }
}
