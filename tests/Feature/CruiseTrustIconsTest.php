<?php

namespace Tests\Feature;

use App\Filament\Pages\CruisePageSettings;
use App\Models\Admin;
use App\Models\Setting;
use App\Support\StripIcons;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Cruise "Why book with us" strip: gold line icons picked by name (5 Nov 2026). */
class CruiseTrustIconsTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_emoji_badges_map_to_line_icons(): void
    {
        $this->assertSame('ship', StripIcons::resolve('🚢'));
        $this->assertSame('dining', StripIcons::resolve('🍽️'));
        $this->assertSame('veg', StripIcons::resolve('🙏'));
        $this->assertSame('phone', StripIcons::resolve('📞'));
        $this->assertSame('support', StripIcons::resolve('support'));
        $this->assertSame('star', StripIcons::resolve('🦄'), 'unknown emoji falls back to the star');
        $this->assertSame('star', StripIcons::resolve(null));
    }

    public function test_cruise_page_shows_svg_icons_not_emoji(): void
    {
        Setting::setJson('cruise_page.trust_items', [
            ['icon' => '🚢', 'title' => 'Old Badge', 'desc' => 'Saved with an emoji'],
            ['icon' => 'veg', 'title' => 'New Badge', 'desc' => 'Saved with a name'],
        ]);

        $html = $this->get('/cruises')->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, '<span class="tc-trust-icon"><svg'));
        $this->assertStringNotContainsString('<span class="tc-trust-icon">🚢', $html);
        $this->assertStringContainsString('Old Badge', $html);
    }

    public function test_experience_cards_show_line_icons_for_old_emojis(): void
    {
        $this->assertSame('masks', StripIcons::resolve('🎭'));
        $this->assertSame('glass', StripIcons::resolve('🥂'));
        $this->assertSame('shopping', StripIcons::resolve('🛍️'));
        $this->assertSame('rings', StripIcons::resolve('💍'));

        Setting::setJson('cruise_page.entertainment_items', [['icon' => '🎭', 'name' => 'Balle Balle Show', 'description' => 'Musical']]);
        Setting::setJson('cruise_page.bars_items', [['icon' => 'glass', 'name' => 'Pool Bar', 'description' => 'Sundowners']]);

        $html = $this->get('/cruises')->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(2, substr_count($html, '<div class="tc-exp-card-icon"><svg'));
        $this->assertStringNotContainsString('<div class="tc-exp-card-icon">🎭', $html);
    }

    public function test_admin_form_loads_old_emojis_as_icon_names_and_saves_names(): void
    {
        Setting::setJson('cruise_page.trust_items', [
            ['icon' => '🙏', 'title' => 'Jain & Veg Friendly', 'desc' => 'Veg counters'],
        ]);
        Setting::setJson('cruise_page.entertainment_items', [
            ['icon' => '🎭', 'name' => 'Balle Balle Show', 'description' => 'Musical'],
        ]);
        $admin = Admin::create(['name' => 'Boss', 'email' => 'boss@example.com', 'password' => 'secret-pass', 'role' => 'Super Admin', 'status' => 'Active']);
        $this->actingAs($admin, 'admin');

        $form = Livewire::test(CruisePageSettings::class);
        $this->assertSame(['veg'], array_column(array_values($form->get('data.trust_items')), 'icon'));

        $this->assertSame('masks', array_values($form->get('data.entertainment_items'))[0]['icon']);

        $form->call('save');
        $this->assertSame('veg', Setting::getJson('cruise_page.trust_items')[0]['icon']);
        $this->assertSame('masks', Setting::getJson('cruise_page.entertainment_items')[0]['icon']);
    }
}
