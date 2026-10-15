<?php

namespace Tests\Feature;

use App\Filament\Pages\CruisePageSettings;
use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Saving Cruise Page Settings without uploading photos must keep the cruise page working. */
class CruisePageSettingsSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_without_uploads_stores_no_upload_placeholders(): void
    {
        $admin = Admin::create(['name' => 'Owner', 'email' => 'o@example.com', 'password' => 'secret-pass', 'role' => 'Super Admin', 'status' => 'Active']);
        $this->actingAs($admin, 'admin');

        Livewire::test(CruisePageSettings::class)->call('save')->assertHasNoErrors()
            ->assertRedirect(\App\Filament\Pages\Dashboard::getUrl());

        foreach (Setting::getJson('cruise_page.destination_cards', []) as $card) {
            $this->assertFalse(is_array($card['image_path'] ?? null), 'image_path saved as an upload placeholder');
        }
        $this->get('/cruises')->assertOk();
    }

    public function test_page_reopens_and_saves_again_with_saved_photo_paths(): void
    {
        $admin = Admin::create(['name' => 'Owner', 'email' => 'o@example.com', 'password' => 'secret-pass', 'role' => 'Super Admin', 'status' => 'Active']);
        $this->actingAs($admin, 'admin');
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('cruise/goa.jpg', 'x');
        Setting::setJson('cruise_page.destination_cards', [
            ['city' => 'Goa', 'tag' => 'Party', 'image_url' => null, 'image_path' => 'cruise/goa.jpg'],
            ['city' => 'Kochi', 'tag' => 'Queen', 'image_url' => '/assets/images/tyt-logo.webp', 'image_path' => ['old-uuid' => []]],
        ]);
        Setting::setJson('cruise_page.dining_items', [['name' => 'Starlight', 'description' => 'x', 'image_url' => null, 'image_path' => 'cruise/goa.jpg']]);

        Livewire::test(CruisePageSettings::class)->assertOk()->call('save')->assertHasNoErrors();

        $cards = Setting::getJson('cruise_page.destination_cards', []);
        $this->assertSame('cruise/goa.jpg', $cards[0]['image_path']);
        $this->assertNull($cards[1]['image_path']);
        Livewire::test(CruisePageSettings::class)->assertOk()->call('save')->assertHasNoErrors();
    }

    public function test_cruise_page_survives_old_placeholder_data(): void
    {
        Setting::setJson('cruise_page.destination_cards', [
            ['city' => 'Goa', 'tag' => 'x', 'image_url' => '/assets/images/tyt-logo.webp', 'image_path' => ['485ed601-fa39' => []]],
        ]);

        $this->get('/cruises')->assertOk()->assertSee('Goa');
    }
}
