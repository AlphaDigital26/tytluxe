<?php

namespace App\Filament\Resources\Hotels\RelationManagers;

use App\Filament\Resources\Hotels\HotelResource;
use App\Services\TripJack\TripJackHotelSync;
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
     * Rooms for TripJack hotels usually only exist via the live Pricing API
     * (TripJack's static content is frequently incomplete/empty for rooms —
     * see TripJackHotelSync::syncLiveRoomsFromPricing) and were previously
     * only fetchable via a manual admin button. Auto-fetching once here, the
     * first time an admin opens an empty Room Types tab, makes TripJack rooms
     * show up the same way hotel details already do — without requiring a
     * separate click — while still only calling the live API for hotels an
     * admin actually views, not all ~900 on a schedule.
     */
    public function mount(): void
    {
        parent::mount();

        $hotel = $this->getOwnerRecord();
        if ($hotel->source === 'tripjack' && $hotel->tripjack_hotel_id && ! $hotel->roomTypes()->exists()) {
            $result = app(TripJackHotelSync::class)->syncLiveRoomsFromPricing($hotel);

            if ($result['synced'] > 0) {
                Notification::make()->title("Fetched {$result['synced']} room type(s) from TripJack")->success()->send();
            } elseif ($result['error']) {
                Notification::make()->title('Could not auto-fetch rooms from TripJack')->body($result['error'])->warning()->send();
            }
        }
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
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image_path')->circular(),
                TextColumn::make('name')
                    ->label('Room Type')
                    ->searchable(),
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
                Action::make('fetchTripjackRooms')
                    ->label('Fetch Rooms from TripJack')
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
