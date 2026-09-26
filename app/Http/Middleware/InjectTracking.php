<?php

namespace App\Http\Middleware;

use App\Services\TrackingCodes;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Puts the tracking snippets into every storefront page.
 *
 * Doing it here rather than in the layout means the pixels reach every page
 * the shop will ever have, including ones added later, and that switching
 * them off in admin really does remove them — there is no template left
 * carrying a hard-coded ID.
 */
class InjectTracking
{
    public function __construct(private TrackingCodes $codes)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldInject($request, $response)) {
            return $response;
        }

        $head   = $this->codes->head();
        $body   = $this->codes->body();
        $footer = $this->codes->footer();

        if ($head === '' && $body === '' && $footer === '') {
            return $response;
        }

        $html = $response->getContent();

        if (! is_string($html) || $html === '') {
            return $response;
        }

        if ($head !== '' && str_contains($html, '</head>')) {
            $html = $this->replaceFirst($html, '</head>', $head . "\n</head>");
        }

        if ($body !== '' && preg_match('/<body[^>]*>/i', $html, $m)) {
            $html = $this->replaceFirst($html, $m[0], $m[0] . "\n" . $body);
        }

        if ($footer !== '' && str_contains($html, '</body>')) {
            $html = $this->replaceFirst($html, '</body>', $footer . "\n</body>");
        }

        $response->setContent($html);

        return $response;
    }

    private function shouldInject(Request $request, Response $response): bool
    {
        // The admin panel is not the shop. Tracking a shop owner's own clicks
        // as customer behaviour would poison every number the ads report.
        $panel = (string) config('admin.path', 'admin');

        if ($request->is($panel, $panel . '/*')) {
            return false;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        if (! method_exists($response, 'getContent')) {
            return false;
        }

        $type = (string) $response->headers->get('Content-Type', '');

        return $type === '' || str_contains($type, 'text/html');
    }

    private function replaceFirst(string $haystack, string $needle, string $replace): string
    {
        $at = strpos($haystack, $needle);

        return $at === false
            ? $haystack
            : substr_replace($haystack, $replace, $at, strlen($needle));
    }
}
