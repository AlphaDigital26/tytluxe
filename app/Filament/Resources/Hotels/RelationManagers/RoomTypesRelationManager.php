<?php

namespace App\Filament\Resources\Hotels\RelationManagers;

use App\Filament\Resources\Hotels\HotelResource;
use App\Services\TripJack\TripJackClient;
use App\Services\TripJack\TripJackHotelSync;
use Illuminate\Support\Facades\Cache;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;

class RoomTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'roomTypes';

    /**
     * Rooms for TripJack hotels usually only exist via a live API call —
     * bulk static-content sync (TripJackHotelSync::syncRoomTypes) is
     * frequently empty for rooms — and were previously only fetchable via a
     * manual admin button. Auto-fetching once here, the first time an admin
     * opens an empty Room Types tab, makes TripJack rooms show up the same
     * way hotel details already do — without requiring a separate click —
     * while still only calling the live API for hotels an admin actually
     * views, not all ~900 on a schedule.
     *
     * Tries the single-hotel Static Detail API first (real room images —
     * see TripJackHotelSync::syncRoomImagesFromStaticDetail), falling back
     * to the live Pricing API (no images, but better than nothing) only if
     * TripJack genuinely has no static room content for this hotel either.
     */
    public function mount(): void
    {
        parent::mount();

        $hotel = $this->getOwnerRecord();
        if ($hotel->source === 'tripjack' && $hotel->tripjack_hotel_id && ! $hotel->roomTypes()->exists()) {
            $sync = app(TripJackHotelSync::class);
            $result = $sync->syncRoomImagesFromStaticDetail($hotel);
            if ($result['synced'] === 0) {
                $result = $sync->syncLiveRoomsFromPricing($hotel);
            }

            if ($result['synced'] > 0) {
                Notification::make()->title("Fetched {$result['synced']} room type(s) from TripJack")->success()->send();
            } elseif ($result['error']) {
                Notification::make()->title('Could not auto-fetch rooms from TripJack')->body($result['error'])->warning()->send();
            }
        }
    }

    /**
     * The admin's Room Types list is TripJack's static content catalogue
     * (24+ named variants is normal), which is a different thing entirely
     * from what's actually bookable on the storefront — that's driven by a
     * live Pricing API call for the guest's specific search dates and can
     * be a small fraction of the catalogue. Without this, "24 rooms here,
     * 6 on the site" reads as a bug when it's the two data sources doing
     * what they're supposed to. Runs one sample Pricing check (tomorrow,
     * 2 adults, 1 room — not the guest's actual dates, just a representative
     * snapshot) so the table can flag which rows are live right now, and
     * what meal-basis rate plans (Room Only / Breakfast / Dinner, ...) each
     * one currently has on offer — a single room type routinely has several
     * of these as separate rate options, which TripJack calls `mealBasis`
     * per option rather than per room, so it's otherwise invisible here.
     *
     * Cached briefly per hotel since this method is called on every table
     * render (Livewire) and would otherwise hit TripJack's live API far
     * more than the sample it produces is worth.
     *
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, string>>
     *         keyed by tripjack_room_code, each value the distinct meal
     *         plans currently on offer for that room.
     */
    protected function liveRoomInfo(): \Illuminate\Support\Collection
    {
        $hotel = $this->getOwnerRecord();

        if ($hotel->source !== 'tripjack' || ! $hotel->tripjack_hotel_id) {
            return collect();
        }

        return Cache::remember(
            "tripjack_live_room_info:{$hotel->id}",
            now()->addMinutes(15),
            function () use ($hotel) {
                try {
                    $response = app(TripJackClient::class)->pricing(
                        $hotel->tripjack_hotel_id,
                        now()->addDay()->format('Y-m-d'),
                        now()->addDays(2)->format('Y-m-d'),
                        [['adults' => 2]],
                        TripJackClient::newCorrelationId(),
                    );
                } catch (\Throwable $e) {
                    return collect();
                }

                return collect($response['options'] ?? [])
                    ->flatMap(function ($option) {
                        $mealBasis = $option['mealBasis'] ?? null;

                        return collect($option['roomInfo'] ?? [])
                            ->pluck('id')
                            ->filter()
                            ->map(fn ($code) => ['code' => (string) $code, 'meal' => $mealBasis]);
                    })
                    ->groupBy('code')
                    ->map(fn ($rows) => $rows->pluck('meal')->filter()->unique()->values());
            }
        );
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Room Details')->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Placeholder::make('tripjack_images_preview')
                        ->label('Synced from TripJack')
                        ->visible(fn ($record) => $record && (
                            str_starts_with((string) $record->image_path, 'http')
                            || collect($record->images ?? [])->contains(fn ($url) => str_starts_with((string) $url, 'http'))
                        ))
                        ->content(function ($record) {
                            $urls = collect([$record->image_path])
                                ->merge($record->images ?? [])
                                ->filter(fn ($url) => str_starts_with((string) $url, 'http'))
                                ->unique()
                                ->values();

                            $html = '<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(120px,1fr)); gap:10px;">';
                            foreach ($urls as $url) {
                                $html .= '<div style="border-radius:8px; overflow:hidden; border:1px solid rgba(255,255,255,0.1);">'
                                    .'<img src="'.e($url).'" loading="lazy" style="width:100%; height:90px; object-fit:cover; display:block;">'
                                    .'</div>';
                            }
                            $html .= '</div>';

                            return new \Illuminate\Support\HtmlString($html);
                        })
                        ->columnSpanFull(),
                    FileUpload::make('image_path')
                        ->disk('public')
                        ->label('Main Thumbnail')
                        ->helperText('Only needed for a manually-added room. TripJack-synced thumbnails are shown above.')
                        ->image()
                        ->saveUploadedFileUsing(fn ($file) => app(\App\Services\ImageOptimizer::class)->optimizeAndSave($file, 'thumbnail', 'room-images')),
                    FileUpload::make('images')
                        ->disk('public')
                        ->label('Gallery Images (Multiple)')
                        ->multiple()
                        ->image()
                        ->reorderable()
                        ->saveUploadedFileUsing(fn ($file) => app(\App\Services\ImageOptimizer::class)->optimizeAndSave($file, 'thumbnail', 'room-images')),
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2),

                Section::make('Specifications & Occupancy')->schema([
                    TextInput::make('room_size')
                        ->placeholder('e.g. 300 sq.ft'),
                    TextInput::make('bed_type')
                        ->placeholder('e.g. 1 King Bed'),
                    TextInput::make('occupancy_adults')
                        ->required()
                        ->numeric()
                        ->default(2),
                    TextInput::make('occupancy_children')
                        ->required()
                        ->numeric()
                        ->default(0),
                ])->columns(2),

                Section::make('Room Settings')->schema([
                    Placeholder::make('room_pricing_note')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString(
                            '<div style="background: rgba(201,168,76,0.08); border: 1px solid rgba(201,168,76,0.3); border-radius: 8px; padding: 12px 16px; font-size: 13px; line-height: 1.6; color: #e8c96b;">'
                            . '💡 <strong>Room Pricing:</strong> You do not need to enter a price. Customers will click "Request Price" and send an enquiry — you then share the price directly with them.'
                            . '</div>'
                        ))
                        ->columnSpanFull(),
                    Select::make('cancellation_policy')
                        ->label('Refund / Cancellation Policy')
                        ->helperText('What happens if the customer cancels their booking?')
                        ->options([
                            'free_cancellation' => '✅  Free Cancellation (customer gets full refund)',
                            'non_refundable'    => '❌  Non-Refundable (no refund on cancellation)',
                            'partial'           => '⚠️  Partial Refund (partial amount returned)',
                        ])
                        ->nullable()
                        ->native(false),
                    TagsInput::make('inclusions')
                        ->label("What's Included in this Room?")
                        ->helperText('Type each inclusion and press Enter — e.g. Free Breakfast, Free WiFi, Swimming Pool Access')
                        ->placeholder('Type and press Enter...')
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label('Show this Room on the Website')
                        ->helperText('Turn OFF to hide this room type from customers without deleting it.')
                        ->default(true)
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        $liveInfo = $this->liveRoomInfo();

        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(function ($query) use ($liveInfo) {
                if ($liveInfo->isEmpty()) {
                    return $query;
                }

                $codes = $liveInfo->keys()->all();
                $placeholders = implode(',', array_fill(0, count($codes), '?'));

                return $query->orderByRaw("CASE WHEN tripjack_room_code IN ({$placeholders}) THEN 0 ELSE 1 END", $codes);
            })
            ->defaultSort('name')
            ->columns([
                ImageColumn::make('image_path')->circular(),
                TextColumn::make('name')
                    ->label('Room Type')
                    ->searchable(),
                TextColumn::make('tripjack_room_code')
                    ->label('Bookable Now')
                    ->visible(fn () => $this->getOwnerRecord()->source === 'tripjack')
                    ->badge()
                    ->state(fn ($record) => $liveInfo->has($record->tripjack_room_code) ? 'Live' : 'Catalog only')
                    ->color(fn ($record) => $liveInfo->has($record->tripjack_room_code) ? 'success' : 'gray')
                    ->tooltip('Checked against a sample 1-night search (tomorrow, 2 adults) — actual availability varies by the dates a guest searches.'),
                TextColumn::make('rate_plans')
                    ->label('Rate Plans')
                    ->visible(fn () => $this->getOwnerRecord()->source === 'tripjack')
                    ->badge()
                    ->separator(',')
                    ->state(fn ($record) => $liveInfo->get($record->tripjack_room_code, collect())->all())
                    ->placeholder('—')
                    ->tooltip('Meal-basis rate plans currently on offer for this room in the sample search (e.g. Room Only, Breakfast, Dinner).'),
                TextColumn::make('occupancy_adults')
                    ->label('Adults')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('occupancy_children')
                    ->label('Children')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cancellation_policy')
                    ->label('Refund Policy')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'free_cancellation' => 'Free Cancellation',
                        'non_refundable'    => 'Non-Refundable',
                        'partial'           => 'Partial Refund',
                        default             => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'free_cancellation' => 'success',
                        'non_refundable'    => 'danger',
                        'partial'           => 'warning',
                        default             => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->label('Visible')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('fetchTripjackRoomImages')
                    ->label('Fetch Room Images from TripJack')
                    ->icon('heroicon-o-photo')
                    ->visible(fn () => $this->getOwnerRecord()->source === 'tripjack' && $this->getOwnerRecord()->tripjack_hotel_id)
                    ->action(function () {
                        $result = app(TripJackHotelSync::class)->syncRoomImagesFromStaticDetail($this->getOwnerRecord());

                        if ($result['error']) {
                            Notification::make()->title('Could not fetch room images')->body($result['error'])->danger()->send();

                            return;
                        }

                        Notification::make()->title("Synced {$result['synced']} room type(s) with real TripJack photos")->success()->send();
                    }),
                Action::make('fetchTripjackRooms')
                    ->label('Fetch Rooms from TripJack (no images)')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn () => $this->getOwnerRecord()->source === 'tripjack' && $this->getOwnerRecord()->tripjack_hotel_id)
                    ->action(function () {
                        $result = app(TripJackHotelSync::class)->syncLiveRoomsFromPricing($this->getOwnerRecord());

                        if ($result['error']) {
                            Notification::make()->title('Could not fetch rooms')->body($result['error'])->danger()->send();

                            return;
                        }

                        Notification::make()->title("Synced {$result['synced']} room type(s) from TripJack")->success()->send();
                    }),
                CreateAction::make()
                    ->after(fn () => redirect(HotelResource::getUrl('index'))),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(fn () => redirect(HotelResource::getUrl('index'))),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
