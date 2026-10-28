<?php

namespace Tests\Feature;

use App\Filament\Widgets\BusinessSnapshot;
use App\Models\Amenity;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fixes from the admin walkthrough (26 Oct 2026): junk and duplicate
 * amenities from TripJack, and dashboard growth figures like "Up 18036%".
 */
class AdminWalkthroughFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_amenity_names_that_differ_only_in_dashes_or_case_match(): void
    {
        $this->assertSame(Amenity::matchKey('24 Hour Front Desk'), Amenity::matchKey('24-hour front desk'));
        $this->assertSame(Amenity::matchKey('Dry cleaning/laundry service'), Amenity::matchKey('Dry cleaning / laundry service'));
        $this->assertNotSame(Amenity::matchKey('Pool'), Amenity::matchKey('Pool bar'));

        $this->assertTrue(Amenity::isMeaningless('1'));
        $this->assertTrue(Amenity::isMeaningless(' 24 '));
        $this->assertFalse(Amenity::isMeaningless('Spa'));
        $this->assertFalse(Amenity::isMeaningless('220V power supply'));
    }

    public function test_clean_up_merges_duplicates_without_any_hotel_losing_one(): void
    {
        [$a, $b, $c] = Hotel::factory()->count(3)->create();
        $keep = Amenity::create(['name' => '24-hour front desk', 'type' => 'hotel']);
        $dup = Amenity::create(['name' => '24 Hour Front Desk', 'type' => 'hotel']);
        $junk = Amenity::create(['name' => '3', 'type' => 'hotel']);
        $keep->hotels()->attach([$a->id, $b->id]);
        $dup->hotels()->attach([$b->id, $c->id]);
        $junk->hotels()->attach($a->id);

        // Without --delete nothing changes.
        $this->artisan('app:clean-amenities')->assertSuccessful();
        $this->assertSame(3, Amenity::count());

        $this->artisan('app:clean-amenities --delete')->assertSuccessful();

        $this->assertSame(['24-hour front desk'], Amenity::pluck('name')->all());
        $this->assertEqualsCanonicalizing([$a->id, $b->id, $c->id], $keep->hotels()->pluck('hotels.id')->all());
    }

    private function asSuperAdmin(): void
    {
        $this->actingAs(\App\Models\Admin::forceCreate(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => bcrypt('x'), 'role' => 'Super Admin', 'status' => 'Active']), 'admin');
    }

    public function test_new_package_form_opens_without_blank_rows_and_saves_with_basics_only(): void
    {
        $this->asSuperAdmin();

        $form = \Livewire\Livewire::test(\App\Filament\Resources\Packages\Pages\CreatePackage::class);
        foreach (['highlights', 'itineraryDays', 'stays', 'departures', 'inclusions', 'exclusions'] as $section) {
            $this->assertEmpty($form->get('data.'.$section), "$section opens with a blank row");
        }

        $form->set('data.title', 'Goa Weekend')->set('data.slug', 'goa-weekend')->set('data.description', 'Two nights by the sea.')
            ->set('data.region_type', 'domestic')->set('data.tour_type', 'custom')->set('data.price_from', 9999)->set('data.duration_nights', 2)
            ->call('create')->assertHasNoFormErrors();
        $this->assertTrue(\App\Models\Package::where('slug', 'goa-weekend')->exists());
    }

    public function test_a_cleared_peak_date_extra_saves_as_no_extra(): void
    {
        $this->asSuperAdmin();

        \Livewire\Livewire::test(\App\Filament\Resources\Packages\Pages\CreatePackage::class)
            ->set('data.title', 'Manali Group')->set('data.slug', 'manali-group')->set('data.description', 'Snow trip.')
            ->set('data.region_type', 'domestic')->set('data.tour_type', 'group')->set('data.price_from', 20000)->set('data.duration_nights', 4)
            ->set('data.departures', ['row' => ['start_date' => '2026-12-10', 'end_date' => '2026-12-14', 'total_seats' => 20, 'extra_price' => null]])
            ->call('create')->assertHasNoFormErrors();

        $this->assertEquals(0, \App\Models\Package::where('slug', 'manali-group')->firstOrFail()->departures()->value('extra_price'));
    }

    public function test_new_destination_keeps_the_sections_the_admin_chose(): void
    {
        $this->asSuperAdmin();
        \Illuminate\Support\Facades\Queue::fake();

        \Livewire\Livewire::test(\App\Filament\Resources\Destinations\Pages\CreateDestination::class)
            ->set('data.name', 'Bali')->set('data.slug', 'bali')->set('data.country', 'Indonesia')
            ->set('data.type', 'island')->set('data.for', ['package'])->set('data.is_active', true)
            ->call('create')->assertHasNoFormErrors();

        $this->assertSame(['package'], \App\Models\Destination::where('slug', 'bali')->value('for'));
        // A packages-only destination doesn't start a hotel import.
        \Illuminate\Support\Facades\Queue::assertNotPushed(\App\Jobs\SyncDestinationHotels::class);
    }

    public function test_blog_publish_date_schedules_posts_and_newest_come_first(): void
    {
        $cat = \App\Models\BlogCategory::create(['name' => 'Tips', 'slug' => 'tips', 'is_active' => true]);
        $make = fn ($slug, $when) => \App\Models\BlogPost::create(['title' => $slug, 'slug' => $slug, 'body' => 'Text', 'blog_category_id' => $cat->id, 'is_active' => true, 'sort_order' => 0, 'published_at' => $when]);
        $make('older-post', now()->subDays(5));
        $make('newer-post', now()->subDay());
        $make('scheduled-post', now()->addDays(3));

        $html = $this->get('/blog')->assertOk()->getContent();
        $this->assertLessThan(strpos($html, '/blog/older-post'), strpos($html, '/blog/newer-post'));
        $this->assertStringNotContainsString('/blog/scheduled-post', $html);
        $this->get('/blog/scheduled-post')->assertNotFound();
        $this->get('/blog/newer-post')->assertOk();
        // No featured destinations yet: visitors don't see an admin note.
        $this->assertStringNotContainsString('Add some from the admin panel', $html);
    }

    public function test_a_blog_category_can_be_added_from_the_post_form(): void
    {
        $this->asSuperAdmin();

        $form = \Livewire\Livewire::test(\App\Filament\Resources\BlogPosts\Pages\CreateBlogPost::class);
        $form->callFormComponentAction('blog_category_id', 'createOption', data: ['name' => 'Travel Tips']);

        $cat = \App\Models\BlogCategory::where('name', 'Travel Tips')->firstOrFail();
        $this->assertSame('travel-tips', $cat->slug);
        $this->assertEquals($cat->id, $form->get('data.blog_category_id'));
    }

    public function test_dashboard_says_big_jumps_in_words_not_huge_percentages(): void
    {
        $widget = new class extends BusinessSnapshot
        {
            public function say(array $series, bool $money = false): string
            {
                return $this->change($series, $money);
            }
        };

        $this->assertSame('Up 20% on last month', $widget->say([100, 120]));
        $this->assertSame('Down 50% on last month', $widget->say([100, 50]));
        $this->assertSame('About 4 times last month', $widget->say([100, 400]));
        $this->assertSame('Much higher than last month (₹14,500)', $widget->say([14500, 2627862], true));
        $this->assertSame('Up from nothing last month', $widget->say([0, 5]));
    }
}
