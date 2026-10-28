<?php

namespace App\Console\Commands;

use App\Models\Amenity;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Tidies the amenity list brought in by the TripJack sync:
 *  - removes "amenities" that are really hotel names, or bare numbers ("1", "2");
 *  - merges near-duplicates ("24 Hour Front Desk" / "24-hour front desk") into
 *    the one most hotels use, moving every hotel, cruise and package onto it.
 * Lists everything first; nothing changes without --delete. --unused also
 * removes amenities nothing uses.
 */
class CleanAmenities extends Command
{
    protected $signature = 'app:clean-amenities
                            {--unused : Also remove amenities that nothing uses}
                            {--delete : Actually make the changes (without it, only lists)}';

    protected $description = 'List (and with --delete, fix) junk and duplicate amenities';

    private const PIVOTS = ['amenity_hotel' => 'hotel_id', 'amenity_cruise' => 'cruise_id', 'amenity_package' => 'package_id'];

    public function handle(): int
    {
        Amenity::forgetHotelTitles();
        $all = Amenity::query()->withCount('hotels')->get();

        $junk = $all->filter(fn (Amenity $a) => Amenity::isMeaningless($a->name)
            || ($a->type === 'hotel' && Amenity::looksLikeHotelName($a->name)));
        if ($this->option('unused')) {
            $unused = Amenity::query()->doesntHave('hotels')->doesntHave('cruises')->doesntHave('packages')->pluck('id');
            $junk = $junk->merge($all->whereIn('id', $unused))->unique('id');
        }

        // Duplicates within a section, among what's left: keep the one most hotels use.
        $merges = $all->whereNotIn('id', $junk->pluck('id'))
            ->groupBy(fn (Amenity $a) => $a->type.'|'.Amenity::matchKey($a->name))
            ->filter(fn (Collection $group) => $group->count() > 1)
            ->map(function (Collection $group) {
                $keep = $group->sortByDesc('hotels_count')->first();

                return ['keep' => $keep, 'drop' => $group->reject(fn ($a) => $a->id === $keep->id)->values()];
            })->values();

        if ($junk->isEmpty() && $merges->isEmpty()) {
            $this->info('Nothing to clean.');

            return self::SUCCESS;
        }

        if ($junk->isNotEmpty()) {
            $this->line('Remove:');
            $this->table(['ID', 'Name', 'Section', 'Used by hotels'], $junk->sortBy('name')
                ->map(fn (Amenity $a) => [$a->id, $a->name, $a->type, $a->hotels_count])->all());
        }
        if ($merges->isNotEmpty()) {
            $this->line('Merge:');
            $this->table(['Keep', 'Merge into it'], $merges->map(fn ($m) => [
                $m['keep']->name, $m['drop']->pluck('name')->implode(', '),
            ])->all());
        }

        if (! $this->option('delete')) {
            $this->warn($junk->count().' to remove and '.$merges->sum(fn ($m) => $m['drop']->count())
                .' duplicates to merge. Nothing was changed — run again with --delete to apply.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($junk, $merges) {
            foreach ($merges as $m) {
                $dropIds = $m['drop']->pluck('id');
                foreach (self::PIVOTS as $pivot => $owner) {
                    // Point each hotel/cruise/package at the kept amenity, unless it already has it.
                    $has = DB::table($pivot)->where('amenity_id', $m['keep']->id)->pluck($owner);
                    DB::table($pivot)->whereIn('amenity_id', $dropIds)->whereNotIn($owner, $has)
                        ->orderBy($owner)->get()->unique($owner)
                        ->each(fn ($row) => DB::table($pivot)->insert(['amenity_id' => $m['keep']->id, $owner => $row->{$owner}]));
                    DB::table($pivot)->whereIn('amenity_id', $dropIds)->delete();
                }
                Amenity::whereIn('id', $dropIds)->delete();
            }

            $ids = $junk->pluck('id');
            foreach (array_keys(self::PIVOTS) as $pivot) {
                DB::table($pivot)->whereIn('amenity_id', $ids)->delete();
            }
            Amenity::whereIn('id', $ids)->delete();
        });

        $this->info('Removed '.$junk->count().' amenities and merged '.$merges->sum(fn ($m) => $m['drop']->count()).' duplicates.');

        return self::SUCCESS;
    }
}
