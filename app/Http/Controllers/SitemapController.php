<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Hotel;
use App\Models\Package;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * /sitemap.xml, built from the database so every hotel, package and blog
 * post the website shows is listed, with the date it last changed (Google
 * uses that to decide what to re-crawl). Cached for an hour.
 */
class SitemapController extends Controller
{
    /** Date the fixed pages (about, terms…) last changed in a meaningful way. */
    protected const STATIC_PAGES_UPDATED = '2026-10-15';

    public function index(): Response
    {
        // Per host, so a visit via another address (www., an IP) can't fill
        // the cache with links on that address.
        $xml = Cache::remember('sitemap.xml:'.request()->getHost(), now()->addHour(), fn () => view('sitemap', [
            'pages' => $this->staticPages(),
            'hotels' => Hotel::visibleOnWebsite()->whereNotNull('slug')->orderBy('id')->get(['slug', 'updated_at']),
            'packages' => Package::where('is_active', true)->whereNotNull('slug')->orderBy('id')->get(['slug', 'updated_at']),
            'posts' => BlogPost::live()->whereNotNull('slug')->orderBy('id')->get(['slug', 'updated_at']),
        ])->render());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /** @return array<int, array{url: string, lastmod: string}> */
    protected function staticPages(): array
    {
        $listUpdated = fn (string $model) => optional($model::max('updated_at'), fn ($d) => \Carbon\Carbon::parse($d)->toDateString()) ?? self::STATIC_PAGES_UPDATED;

        return [
            ['url' => route('home'), 'lastmod' => self::STATIC_PAGES_UPDATED],
            ['url' => route('hotels'), 'lastmod' => $listUpdated(Hotel::class)],
            ['url' => route('packages'), 'lastmod' => $listUpdated(Package::class)],
            ['url' => route('flights'), 'lastmod' => self::STATIC_PAGES_UPDATED],
            ['url' => route('cruises'), 'lastmod' => $listUpdated(\App\Models\Cruise::class)],
            ['url' => route('offers'), 'lastmod' => $listUpdated(\App\Models\Offer::class)],
            ['url' => route('blog'), 'lastmod' => $listUpdated(BlogPost::class)],
            ['url' => route('about'), 'lastmod' => self::STATIC_PAGES_UPDATED],
            ['url' => route('contact'), 'lastmod' => self::STATIC_PAGES_UPDATED],
            ['url' => route('faqs'), 'lastmod' => self::STATIC_PAGES_UPDATED],
            ['url' => route('help'), 'lastmod' => self::STATIC_PAGES_UPDATED],
            ['url' => route('cancellation'), 'lastmod' => self::STATIC_PAGES_UPDATED],
            ['url' => route('terms'), 'lastmod' => self::STATIC_PAGES_UPDATED],
            ['url' => route('privacy'), 'lastmod' => self::STATIC_PAGES_UPDATED],
        ];
    }
}
