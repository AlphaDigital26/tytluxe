<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/** Guests pick Nationality and Country of Residence in the hotel search bar. */
class HotelNationalitySearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::put('tripjack_nationalities', [
            ['countryId' => '106', 'countryName' => 'India'],
            ['countryId' => '226', 'countryName' => 'United Kingdom'],
        ], now()->addDay());
    }

    public function test_search_bar_offers_both_pills_with_india_by_default(): void
    {
        $this->get(route('hotels'))
            ->assertOk()
            ->assertSee('Nationality:')
            ->assertSee('Country of Residence:')
            ->assertSee('name="nationality" class="htl-cc-input" id="htlNationalityInput" value="106"', false)
            ->assertSee('name="residence" class="htl-cc-input" id="htlResidenceInput" value="106"', false);
    }

    public function test_chosen_countries_stay_selected(): void
    {
        $this->get(route('hotels', ['nationality' => '226', 'residence' => '226']))
            ->assertOk()
            ->assertSee('id="htlNationalityInput" value="226"', false)
            ->assertSee('id="htlResidenceInput" value="226"', false)
            ->assertSeeInOrder(['Nationality:', 'United Kingdom', 'Country of Residence:', 'United Kingdom']);
    }

    public function test_unknown_country_falls_back_to_india(): void
    {
        $this->get(route('hotels', ['nationality' => '99999', 'residence' => 'abc']))
            ->assertOk()
            ->assertSee('id="htlNationalityInput" value="106"', false)
            ->assertSee('id="htlResidenceInput" value="106"', false);
    }
}
