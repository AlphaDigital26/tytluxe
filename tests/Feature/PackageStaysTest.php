<?php

namespace Tests\Feature;

use App\Filament\Resources\Packages\Pages\EditPackage;
use App\Models\Admin;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\Package;
use App\Models\PackageStay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** "Where You'll Stay": the hotels and camps inside a package. */
class PackageStaysTest extends TestCase
{
    use RefreshDatabase;

    protected function package(): Package
    {
        return Package::create([
            'title' => 'Manali Kasol 4 Nights', 'slug' => 'manali-kasol', 'description' => 'Mountains and river.',
            'region_type' => 'domestic', 'tour_type' => 'group', 'duration_nights' => 4, 'price_from' => 15000, 'is_active' => true,
        ]);
    }

    protected function hotel(array $attributes = []): Hotel
    {
        $manali = Destination::firstOrCreate(['slug' => 'manali'], ['name' => 'Manali', 'country' => 'India']);

        return Hotel::create($attributes + [
            'destination_id' => $manali->id, 'title' => 'Snow Valley Resort', 'slug' => 'snow-valley-resort', 'description' => 'x',
            'category' => 'city_luxury', 'address' => 'Manali', 'star_rating' => 4, 'price_from' => 0, 'is_active' => true,
        ]);
    }

    public function test_package_page_shows_linked_hotels_and_typed_in_stays(): void
    {
        $package = $this->package();
        $hotel = $this->hotel();
        $hotel->images()->create(['path' => 'https://cdn.example.com/snow-valley.jpg', 'sort_order' => 1, 'is_hidden' => false]);
        $package->inclusions()->create(['label' => '4 nights in hotels and camps', 'category' => 'hotel']);

        $package->stays()->create(['hotel_id' => $hotel->id, 'nights' => 2, 'room_type' => 'Deluxe room, triple sharing', 'meal_plan' => 'Breakfast & dinner', 'sort_order' => 1]);
        $package->stays()->create(['name' => 'Riverside Camps', 'stay_type' => 'camp', 'location' => 'Kasol', 'nights' => 2, 'or_similar' => false, 'note' => 'River-facing tents', 'sort_order' => 2]);

        $this->get(route('package.details', $package->slug))->assertOk()
            ->assertSee('Where You', false)
            ->assertSeeInOrder(['href="#stays"', 'Snow Valley Resort', 'Riverside Camps'], false)
            ->assertSee('2 stays · 4 nights · Manali, Kasol')
            ->assertSee('https://cdn.example.com/snow-valley.jpg', false)     // the hotel's own photo
            ->assertSee('★★★★')                                              // the hotel's stars
            ->assertSee(route('hotel.details', 'snow-valley-resort'), false) // "View hotel"
            ->assertSee('Deluxe room, triple sharing')
            ->assertSee('River-facing tents')
            ->assertSee('Subject to availability')
            ->assertSee('See the hotels');                                   // from the hotel inclusion
    }

    public function test_no_link_to_a_hotel_guests_cannot_open_and_no_section_without_stays(): void
    {
        $package = $this->package();
        $this->get(route('package.details', $package->slug))->assertOk()->assertDontSee('id="stays"', false);

        $hidden = $this->hotel(['is_active' => false]);
        $package->stays()->create(['hotel_id' => $hidden->id, 'nights' => 4]);

        $this->get(route('package.details', $package->slug))->assertOk()
            ->assertSee('Snow Valley Resort')
            ->assertDontSee(route('hotel.details', 'snow-valley-resort'), false);
    }

    public function test_admin_adds_stays_on_the_package_form(): void
    {
        $this->actingAs(Admin::create(['name' => 'Ops', 'email' => 'ops@example.com', 'password' => 'secret-pass', 'role' => 'Operations', 'status' => 'Active']), 'admin');
        $package = $this->package();
        $hotel = $this->hotel();

        Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()])
            ->set('data.stays', [
                'a' => ['hotel_id' => $hotel->id, 'name' => null, 'stay_type' => 'hotel', 'nights' => 2, 'or_similar' => true],
                'b' => ['hotel_id' => null, 'name' => 'Riverside Camps', 'stay_type' => 'camp', 'location' => 'Kasol', 'nights' => 2, 'or_similar' => false],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $stays = $package->stays()->get();
        $this->assertSame(['Snow Valley Resort', 'Riverside Camps'], $stays->map->displayName()->all());
        $this->assertSame(['Manali', 'Kasol'], $stays->map->displayLocation()->all());
    }

    public function test_stay_nights_cannot_add_up_to_more_than_the_package_nights(): void
    {
        $this->actingAs(Admin::create(['name' => 'Ops', 'email' => 'ops4@example.com', 'password' => 'secret-pass', 'role' => 'Operations', 'status' => 'Active']), 'admin');
        $package = $this->package(); // a 4-night package
        $stay = fn (string $name, int $nights) => ['hotel_id' => null, 'name' => $name, 'stay_type' => 'hotel', 'nights' => $nights, 'or_similar' => true];

        // 3 + 2 = 5 nights on a 4-night package: refused, nothing saved.
        Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()])
            ->set('data.stays', ['a' => $stay('Manali Inn', 3), 'b' => $stay('Kasol Camps', 2)])
            ->assertSee('Nights used: 5 of 4 (4-night package).')
            ->call('save')
            ->assertHasFormErrors(['stays']);
        $this->assertSame(0, PackageStay::count());

        // Raising the package to 5 nights in the Pricing tab lets the same stays save.
        Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()])
            ->set('data.stays', ['a' => $stay('Manali Inn', 3), 'b' => $stay('Kasol Camps', 2)])
            ->set('data.duration_nights', 5)
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame(5, (int) PackageStay::sum('nights'));

        // 2 + 2 = 4 on a 4-night package: fine.
        $package->update(['duration_nights' => 4]);
        Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()])
            ->set('data.stays', ['a' => $stay('Manali Inn', 2), 'b' => $stay('Kasol Camps', 2)])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame(4, (int) PackageStay::sum('nights'));
    }

    public function test_a_typed_in_stay_needs_a_name(): void
    {
        $this->actingAs(Admin::create(['name' => 'Ops', 'email' => 'ops2@example.com', 'password' => 'secret-pass', 'role' => 'Operations', 'status' => 'Active']), 'admin');
        $package = $this->package();

        Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()])
            ->set('data.stays', ['a' => ['hotel_id' => null, 'name' => '', 'stay_type' => 'camp', 'nights' => 2, 'or_similar' => true]])
            ->call('save')
            ->assertHasFormErrors(['stays.a.name' => 'required']);

        $this->assertSame(0, PackageStay::count());
    }

    public function test_a_hotel_from_the_list_cannot_be_renamed_and_a_typed_in_stay_locks_the_list(): void
    {
        $this->actingAs(Admin::create(['name' => 'Ops', 'email' => 'ops3@example.com', 'password' => 'secret-pass', 'role' => 'Operations', 'status' => 'Active']), 'admin');
        $package = $this->package();
        $hotel = $this->hotel();

        // Choosing a hotel fills its details in by themselves and locks them.
        $form = Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()])
            ->set('data.stays', ['a' => ['hotel_id' => null, 'name' => 'Wrong Name', 'location' => 'Goa', 'star_rating' => 3, 'stay_type' => 'camp', 'nights' => 2, 'or_similar' => true]])
            ->assertFormFieldDisabled('stays.a.hotel_id')       // a name is typed: the list is locked
            ->set('data.stays.a.name', '')
            ->assertFormFieldEnabled('stays.a.hotel_id')
            ->set('data.stays.a.hotel_id', $hotel->id)
            ->assertSet('data.stays.a.name', 'Snow Valley Resort')
            ->assertSet('data.stays.a.location', 'Manali')
            ->assertSet('data.stays.a.star_rating', 4)
            ->assertSet('data.stays.a.stay_type', 'resort')
            ->assertFormFieldDisabled('stays.a.name')
            ->assertFormFieldDisabled('stays.a.location')
            ->assertFormFieldDisabled('stays.a.star_rating');

        // Even if a name sneaks in, it is not saved on top of the hotel's own.
        $form->set('data.stays.a.name', 'Overwritten')->call('save')->assertHasNoFormErrors();

        $stay = $package->stays()->sole();
        $this->assertNull($stay->name);
        $this->assertSame('Snow Valley Resort', $stay->displayName());
        $this->assertSame('Manali', $stay->displayLocation());
        $this->assertSame(4, $stay->displayStars());

        // Older stays saved with a typed name still show the hotel's real name —
        // on the website, and in the form when the package is opened again.
        $stay->update(['name' => 'Typo Hotel', 'location' => 'Jaipur']);
        $this->assertSame('Snow Valley Resort', $stay->fresh()->displayName());

        $reopened = Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()]);
        $key = array_key_first($reopened->get('data.stays'));
        $reopened->assertSet("data.stays.$key.name", 'Snow Valley Resort')
            ->assertSet("data.stays.$key.location", 'Manali')
            ->assertSet("data.stays.$key.star_rating", 4);

        // Clearing the hotel empties its details, so a name can be typed again.
        $reopened->set("data.stays.$key.hotel_id", null)
            ->assertSet("data.stays.$key.name", null)
            ->assertFormFieldEnabled("stays.$key.name");
    }
}
