<?php

namespace App\Console\Commands;

use App\Models\Amenity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Removes "amenities" that are really hotel names (brought in by the TripJack
 * sync before it learned to skip them). Lists them first; nothing is deleted
 * without --delete. --unused also lists amenities no hotel, cruise or package
 * uses.
 */
class CleanAmenities extends Command
{
    protected $signature = 'app:clean-amenities
                            {--unused : Also remove amenities that nothing uses}
                            {--delete : Actually delete (without it, only lists)}';

    protected $description = 'List (and with --delete, remove) amenities that are hotel names';

    public function handle(): int
    {
        Amenity::forgetHotelTitles();

        $junk = Amenity::query()
            ->where('type', 'hotel')
            ->get()
            ->filter(fn (Amenity $a) => Amenity::looksLikeHotelName($a->name));

        if ($this->option('unused')) {
            $unused = Amenity::query()->doesntHave('hotels')->doesntHave('cruises')->doesntHave('packages')->get();
            $junk = $junk->merge($unused)->unique('id');
        }

        if ($junk->isEmpty()) {
            $this->info('Nothing to clean.');

            return self::SUCCESS;
        }

        $this->table(['ID', 'Name', 'Section', 'Used by hotels'], $junk->sortBy('name')->map(fn (Amenity $a) => [
            $a->id, $a->name, $a->type, $a->hotels()->count(),
        ])->all());

        if (! $this->option('delete')) {
            $this->warn($junk->count().' amenities listed. Nothing was deleted — run again with --delete to remove them.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($junk) {
            $ids = $junk->pluck('id');
            foreach (['amenity_hotel', 'amenity_cruise', 'amenity_package'] as $pivot) {
                DB::table($pivot)->whereIn('amenity_id', $ids)->delete();
            }
            Amenity::whereIn('id', $ids)->delete();
        });

        $this->info('Removed '.$junk->count().' amenities.');

        return self::SUCCESS;
    }
}
