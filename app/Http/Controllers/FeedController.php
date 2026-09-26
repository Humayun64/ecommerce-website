<?php

namespace App\Http\Controllers;

use App\Services\ProductFeed;
use Illuminate\Support\Facades\Cache;

class FeedController extends Controller
{
    public function __construct(private ProductFeed $feed)
    {
    }

    public function facebook()
    {
        return $this->serve('facebook');
    }

    public function google()
    {
        return $this->serve('google');
    }

    private function serve(string $channel)
    {
        abort_unless($this->feed->enabled($channel), 404);

        // Facebook re-reads the feed on a schedule and the catalog can be
        // hundreds of products, so the built XML is held for an hour rather
        // than rebuilt on every fetch.
        $xml = Cache::remember("feed.{$channel}", now()->addHour(), fn () => $this->feed->xml($channel));

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
