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
        $package->stays()->create(['name' => 'Riverside Camps', 'stay_type' => 'camp', 'location' => 'kasol', 'nights' => 2, 'or_similar' => false, 'note' => 'River-facing tents', 'check_in_time' => '13:00', 'check_out_time' => '10:30', 'sort_order' => 2]);

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
            ->assertSeeInOrder(['Check-in 2:00 PM', 'Check-out 11:00 AM', 'Check-in 1:00 PM', 'Check-out 10:30 AM']) // hotel's own times, then the camp's
            ->assertSeeInOrder(['Day 1 → Day 3', 'Day 3 → Day 5']) // worked out from the nights
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
                'b' => ['hotel_id' => null, 'name' => 'Riverside Camps', 'stay_type' => 'camp', 'location' => 'Kasol', 'nights' => 2, 'or_similar' => false, 'check_in_time' => '13:00', 'check_out_time' => '10:30'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $stays = $package->stays()->get();
        $this->assertSame(['Snow Valley Resort', 'Riverside Camps'], $stays->map->displayName()->all());
        $this->assertSame(['Manali', 'Kasol'], $stays->map->displayLocation()->all());
        $this->assertSame(['13:00', '10:30'], [$stays[1]->check_in_time, $stays[1]->check_out_time]);
        $this->assertSame(['2:00 PM', '1:00 PM'], $stays->map->checkInTime()->all()); // the hotel's own, then the camp's
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

    public function test_trip_days_follow_on_unless_set_and_cannot_end_after_the_trip(): void
    {
        // Worked out in order; a set day restarts the count from there.
        $this->assertSame(
            [['in' => 1, 'out' => 3], ['in' => 3, 'out' => 4], ['in' => 5, 'out' => 6], ['in' => 6, 'out' => null]],
            PackageStay::dayPlan([['nights' => 2], ['nights' => 1], ['nights' => 1, 'check_in_day' => 5], ['nights' => null]])
        );

        $this->actingAs(Admin::create(['name' => 'Ops', 'email' => 'ops5@example.com', 'password' => 'secret-pass', 'role' => 'Operations', 'status' => 'Active']), 'admin');
        $package = $this->package(); // 4 nights: the trip ends on Day 5
        $stay = fn (int $nights, ?int $day = null) => ['hotel_id' => null, 'name' => 'Stay', 'stay_type' => 'hotel', 'nights' => $nights, 'check_in_day' => $day, 'or_similar' => true];

        Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()])
            ->set('data.stays', ['a' => $stay(2), 'b' => $stay(2, 4)]) // Day 4 → Day 6
            ->assertSee('2 nights here (Day 4 → Day 6). The trip ends on Day 5')  // red warning straight away
            ->assertSee('Latest: Day 4.')                               // a 4-night trip ends on Day 5
            ->call('save')
            ->assertHasFormErrors(['stays']);

        // A 1-night stay can start on Day 4 at the latest.
        $this->assertSame(4, \App\Filament\Resources\Packages\Schemas\PackageForm::latestCheckInDay(4, 1));
        $this->assertSame(2, \App\Filament\Resources\Packages\Schemas\PackageForm::latestCheckInDay(2, 1)); // your 2N/3D Udaipur trip

        Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()])
            ->set('data.stays', ['a' => $stay(2), 'b' => $stay(2)])     // Day 1 → 3, Day 3 → 5
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame([['in' => 1, 'out' => 3], ['in' => 3, 'out' => 5]], PackageStay::dayPlan($package->stays()->get()->all()));
    }

    public function test_check_in_day_fills_check_out_as_the_next_day_and_check_out_can_be_changed(): void
    {
        $this->actingAs(Admin::create(['name' => 'Ops', 'email' => 'ops6@example.com', 'password' => 'secret-pass', 'role' => 'Operations', 'status' => 'Active']), 'admin');
        $package = $this->package(); // 4 nights: Day 1 → Day 5
        $package->stays()->create(['name' => 'Manali Inn', 'nights' => 1, 'sort_order' => 1]);
        $package->stays()->create(['name' => 'Kasol Camps', 'nights' => 1, 'sort_order' => 2]);

        $form = Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()]);
        [$a, $b] = array_keys($form->get('data.stays'));
        $form->assertSet("data.stays.$a.check_out_day", 2)->assertSet("data.stays.$b.check_out_day", 3)
            ->assertFormFieldDisabled("stays.$a.nights"); // worked out from the days, never typed

        // Check-in Day 1 → check-out fills in as Day 2.
        $form->set("data.stays.$a.check_in_day", 1)
            ->assertSet("data.stays.$a.check_out_day", 2)
            ->assertSet("data.stays.$a.nights", 1);

        // Check-out changed to Day 4 → 3 nights there; the next stay follows on, Day 4 → 5.
        $form->set("data.stays.$a.check_out_day", 4)
            ->assertSet("data.stays.$a.nights", 3)
            ->assertSet("data.stays.$b.check_out_day", 5)
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame([3, 1], $package->stays()->pluck('nights')->all());

        // A new check-in day starts over at one night: Day 2 → Day 3.
        $form->set("data.stays.$a.check_in_day", 2)
            ->assertSet("data.stays.$a.check_out_day", 3)
            ->assertSet("data.stays.$a.nights", 1);

        // Check-out can't be on/before check-in, or after the trip's last day.
        $form->set("data.stays.$b.check_out_day", 3)->call('save')->assertHasFormErrors(["stays.$b.check_out_day" => 'min']);
        $form->set("data.stays.$b.check_out_day", 7)->call('save')->assertHasFormErrors(["stays.$b.check_out_day" => 'max']);
    }

    public function test_two_stays_cannot_overlap(): void
    {
        $this->actingAs(Admin::create(['name' => 'Ops', 'email' => 'ops7@example.com', 'password' => 'secret-pass', 'role' => 'Operations', 'status' => 'Active']), 'admin');
        $package = $this->package(); // 4 nights — 2 + 1 nights fits, so only the overlap is wrong
        $row = fn (string $name, int $nights, ?int $day = null) => ['hotel_id' => null, 'name' => $name, 'stay_type' => 'hotel', 'nights' => $nights, 'check_in_day' => $day, 'or_similar' => true];

        Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()])
            ->set('data.stays', ['a' => $row('Manali Inn', 2), 'b' => $row('Kasol Camps', 1, 2)]) // Day 1 → 3, then Day 2?
            ->call('save')
            ->assertHasFormErrors(['stays'])
            ->assertSee('"Kasol Camps" checks in on Day 2, but the stay above it checks out on Day 3.');
        $this->assertSame(0, PackageStay::count());
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
            ->tap(fn ($c) => $this->assertStringContainsString('14:00', (string) $c->get('data.stays.a.check_in_time')))  // the hotel's 2:00 PM
            ->tap(fn ($c) => $this->assertStringContainsString('11:00', (string) $c->get('data.stays.a.check_out_time'))) // the hotel's 11:00 AM
            ->assertFormFieldDisabled('stays.a.check_in_time')
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
