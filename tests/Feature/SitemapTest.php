<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_main_pages_and_only_live_blog_posts(): void
    {
        DB::table('blog_posts')->insert([
            ['title' => 'Live', 'slug' => 'live-post', 'is_active' => true, 'created_at' => now(), 'updated_at' => '2026-09-01 10:00:00'],
            ['title' => 'Hidden', 'slug' => 'hidden-post', 'is_active' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $xml = simplexml_load_string($response->getContent());

        $this->assertNotFalse($xml, 'sitemap is valid XML');
        $locs = [];
        foreach ($xml->url as $url) {
            $locs[(string) $url->loc] = (string) $url->lastmod;
        }

        $this->assertArrayHasKey(route('home'), $locs);
        $this->assertArrayHasKey(route('hotels'), $locs);
        $this->assertArrayHasKey(route('packages'), $locs);
        $this->assertSame('2026-09-01', $locs[route('blog.details', 'live-post')]);
        $this->assertArrayNotHasKey(route('blog.details', 'hidden-post'), $locs);
    }

    public function test_www_address_redirects_permanently_to_the_main_domain(): void
    {
        config(['app.url' => 'https://tytluxe.in']);

        $this->get('https://www.tytluxe.in/hotels?city=goa')
            ->assertStatus(301)
            ->assertRedirect('https://tytluxe.in/hotels?city=goa');

        // Form posts / webhooks are not redirected (they'd lose their data).
        $this->assertNotSame(301, $this->post('https://www.tytluxe.in/payment/razorpay/webhook')->getStatusCode());
    }
}
