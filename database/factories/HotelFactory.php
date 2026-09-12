<?php

namespace Database\Factories;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hotel>
 */
class HotelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'destination_id' => \App\Models\Destination::factory(),
            'title' => fake()->company() . ' Palace & Resort',
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'category' => 'beach_resort',
            'address' => fake()->address(),
            'star_rating' => 5,
            'price_from' => 20000,
            'is_active' => true,
        ];
    }
}
