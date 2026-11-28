<?php

namespace Tests\Feature;

use App\Filament\Resources\Packages\Pages\EditPackage;
use App\Filament\Resources\Packages\Pages\ListPackages;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Package;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Deleted packages go to a Bin: restorable for 30 days, then erased (never if booked). */
class PackageBinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(Admin::create(['name' => 'Owner', 'email' => 'o@example.com', 'password' => 'secret-pass', 'role' => 'Super Admin', 'status' => 'Active']), 'admin');
    }

    protected function package(string $slug = 'goa-escape'): Package
    {
        $package = Package::create([
            'title' => 'Goa Escape', 'slug' => $slug, 'description' => 'Beaches.', 'tour_type' => 'custom',
            'duration_nights' => 3, 'price_from' => 15000, 'is_active' => true,
        ]);
        $package->departures()->create(['start_date' => today()->addMonth(), 'end_date' => today()->addMonth()->addDays(3), 'total_seats' => 10]);

        return $package;
    }

    public function test_deleting_moves_to_bin_and_restore_brings_it_back(): void
    {
        $package = $this->package();

        Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()])->callAction(DeleteAction::class);

        $this->assertSoftDeleted($package);
        $this->assertDatabaseHas('package_departures', ['package_id' => $package->id]); // details kept
        $this->get('/packages/goa-escape')->assertNotFound(); // gone from the website

        Livewire::test(ListPackages::class)->assertCanNotSeeTableRecords([$package]);
        Livewire::test(ListPackages::class, ['activeTab' => 'bin'])
            ->assertCanSeeTableRecords([$package])
            ->assertSee('Deleted forever on')
            ->assertSee(now()->addDays(Package::BIN_DAYS)->format('j M Y'));

        Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()])
            ->assertSee('This package is in the Bin')
            ->callAction(RestoreAction::class);

        $this->assertNotSoftDeleted($package->fresh());
        $this->get('/packages/goa-escape')->assertOk();
    }

    public function test_nobody_can_delete_forever_by_hand(): void
    {
        $package = $this->package();
        $package->delete();

        Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()])
            ->assertActionVisible(RestoreAction::class)
            ->assertActionDoesNotExist(ForceDeleteAction::class);
        $this->assertSoftDeleted($package);
    }

    public function test_emptying_the_bin_erases_the_package_and_its_details(): void
    {
        $package = $this->package();
        $package->delete();
        $package->forceFill(['deleted_at' => now()->subDays(Package::BIN_DAYS)->subMinute()])->saveQuietly();

        $this->artisan('packages:empty-bin')->assertSuccessful();

        $this->assertDatabaseMissing('packages', ['id' => $package->id]);
        $this->assertDatabaseMissing('package_departures', ['package_id' => $package->id]);
    }

    public function test_bin_is_emptied_after_30_days_but_booked_packages_are_kept(): void
    {
        $old = $this->package('old-one');
        $recent = $this->package('recent-one');
        $booked = $this->package('booked-one');
        Booking::create(['reference' => 'TYTPKG00001', 'vertical' => 'package', 'package_id' => $booked->id, 'status' => 'confirmed',
            'lead_guest_name' => 'Asha', 'base_amount' => 1000, 'tax_amount' => 0, 'total_amount' => 1000, 'currency' => 'INR', 'check_in' => today()]);

        foreach ([$old, $booked] as $p) {
            $p->delete();
            $p->forceFill(['deleted_at' => now()->subDays(Package::BIN_DAYS + 1)])->saveQuietly();
        }
        $recent->delete();

        $this->artisan('packages:empty-bin')->expectsOutputToContain('1 package(s) deleted for good')->assertSuccessful();

        $this->assertDatabaseMissing('packages', ['id' => $old->id]);
        $this->assertSoftDeleted($recent);
        $this->assertSoftDeleted($booked);

        // The booked package says it is kept, and its bookings keep the name.
        Livewire::test(EditPackage::class, ['record' => $booked->getRouteKey()])
            ->assertSee('It has bookings, so it is never deleted for good');
        Livewire::test(ListPackages::class, ['activeTab' => 'bin'])->assertSee('Never — has bookings');
        $this->assertSame('Goa Escape', Booking::where('reference', 'TYTPKG00001')->first()->package->title);
    }
}
