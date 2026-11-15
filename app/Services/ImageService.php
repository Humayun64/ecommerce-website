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

        // Opening a photo takes about five bytes of memory per pixel, far
        // more than its size on disk. A photo too big for this server is
        // stored as it is rather than crashing the page.
        $size = @getimagesize($file->getRealPath());

        if (! $size || ! $this->fitsInMemory((int) $size[0], (int) $size[1])) {
            return null;
        }

        try {
            $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        } catch (\Throwable $e) {
            return null;
        }

        return $image ?: null;
    }

    private function fitsInMemory(int $width, int $height): bool
    {
        if ($width < 1 || $height < 1 || $width * $height > 40_000_000) {
            return false;
        }

        $limit = trim((string) ini_get('memory_limit'));

        if ($limit === '' || $limit === '-1') {
            return true;
        }

        $bytes = (int) $limit * match (strtolower(substr($limit, -1))) {
            'g' => 1024 ** 3, 'm' => 1024 ** 2, 'k' => 1024, default => 1,
        };

        // The photo itself, the resized copy, and room for the rest of the page.
        $needed = $width * $height * 5 + self::MAX_EDGE * self::MAX_EDGE * 5 + 24 * 1024 * 1024;

        return memory_get_usage(true) + $needed < $bytes;
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
