<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Headers that tell the browser to be stricter with this site.
 *
 * There is deliberately no full Content-Security-Policy: the storefront
 * loads Facebook, Google and TikTok tracking scripts chosen from the admin
 * panel, and a strict policy would silently break them.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            // Nobody else may put this site inside a frame (clickjacking).
            'X-Frame-Options'        => 'SAMEORIGIN',
            // The browser must not guess a file's type, so an uploaded
            // picture can never be run as a script.
            'X-Content-Type-Options' => 'nosniff',
            // Other sites see only the domain a visitor came from, never
            // the full address (which can hold an order number).
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=()',
        ];

        // Only promise HTTPS when this request really is HTTPS, so the
        // local http://127.0.0.1 copy keeps working.
        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000';
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
