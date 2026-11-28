<?php

namespace App\Console\Commands;

use App\Models\Destination;
use App\Models\Hotel;
use App\Services\TripJack\TripJackClient;
use App\Services\TripJack\TripJackHotelSync;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Adds specific TripJack hotels to the website by their TripJack hotel ID
 * (e.g. the hotels TripJack asks us to test in UAT). Each hotel goes into
 * the destination for its own city and country, created if the site doesn't
 * have that city yet. Hotels already on the site are refreshed in place.
 */
class ImportTripjackHotels extends Command
{
    protected $signature = 'tripjack:import-hotels {ids* : TripJack hotel IDs, separated by spaces}';

    protected $description = 'Add specific TripJack hotels to the website by their TripJack hotel ID';

    public function handle(TripJackClient $client, TripJackHotelSync $sync): int
    {
        $ids = collect($this->argument('ids'))->map(fn ($id) => trim((string) $id))->filter()->unique()->values();
        $done = [];

        foreach ($ids->chunk(100) as $chunk) {
            try {
                $hotels = $client->fetchHotelContent($chunk->values()->all())['hotels'] ?? [];
            } catch (\Throwable $e) {
                $this->error('TripJack did not answer: '.$e->getMessage());

                return self::FAILURE;
            }

            foreach ($hotels as $detail) {
                $id = (string) ($detail['tjHotelId'] ?? '');
                if ($id === '' || ! $chunk->contains($id)) {
                    continue;
                }
                $destination = Hotel::where('tripjack_hotel_id', $id)->first()?->destination
                    ?? $this->destinationFor($detail);
                if (! $destination) {
                    $this->warn("{$id}  skipped: TripJack gives no city/country for it");

                    continue;
                }

                $sync->upsertHotel($detail, $destination->id);
                $done[] = $id;
                $this->line("{$id}  {$detail['name']}  →  {$destination->name}, {$destination->country}");
            }
        }

        foreach ($ids->diff($done) as $missing) {
            $this->warn("{$missing}  not found at TripJack");
        }
        $this->info(count($done).' of '.$ids->count().' hotels added or updated.');

        return self::SUCCESS;
    }

    /** The site's destination for the hotel's city + country, created if missing. */
    protected function destinationFor(array $detail): ?Destination
    {
        $address = $detail['locale']['address'] ?? [];
        $city = trim((string) ($address['city'] ?? ''));
        $country = trim((string) ($address['countryname'] ?? ''));
        if ($city === '' || $country === '') {
            return null;
        }

        $existing = Destination::whereRaw('LOWER(name) = ?', [strtolower($city)])
            ->whereRaw('LOWER(country) = ?', [strtolower($country)])
            ->first();
        if ($existing) {
            return $existing;
        }

        $slug = Str::slug($city);
        if (Destination::where('slug', $slug)->exists()) {
            $slug .= '-'.Str::slug($country); // same city name in another country
        }

        return Destination::create([
            'slug' => $slug,
            'name' => Str::title(strtolower($city)),
            'country' => Str::title(strtolower($country)),
            'type' => 'city',
            'for' => ['hotel'],
            'is_active' => true,
        ]);
    }
}
