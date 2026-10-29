<?php

namespace App\Filament\Resources\ItineraryDownloads\Tables;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Models\Enquiry;
use App\Models\ItineraryDownload;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * People who downloaded a package itinerary — leads to follow up. The same
 * person often downloads several times, so by default each person is shown
 * once (their latest download) with how many times they downloaded.
 */
class ItineraryDownloadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('package')->addSelect([
                'downloads_by_person' => ItineraryDownload::query()->from('itinerary_downloads as d2')
                    ->selectRaw('count(*)')->whereColumn('d2.phone', 'itinerary_downloads.phone'),
            ]))
            ->columns([
                TextColumn::make('name')
                    ->label('Person')
                    ->description(fn (ItineraryDownload $record) => $record->phone)
                    ->searchable(['name', 'phone', 'email']),
                TextColumn::make('package.title')
                    ->label('Package')
                    ->wrap()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('downloads_by_person')
                    ->label('Downloads')
                    ->badge()
                    ->color(fn ($state) => (int) $state > 1 ? 'warning' : 'gray')
                    ->tooltip('How many times this person downloaded an itinerary — more than once means they are keen.'),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Downloaded on')
                    ->dateTime('j M Y, g:i A')
                    ->sortable(),
                TextColumn::make('enquiry_status')
                    ->label('Follow-up')
                    ->state(fn (ItineraryDownload $record) => self::enquiryFor($record) ? 'Enquiry made' : 'Not yet')
                    ->badge()
                    ->color(fn ($state) => $state === 'Enquiry made' ? 'success' : 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Search by name, phone, email or package')
            ->filters([
                Filter::make('latest_per_person')
                    ->label('Show each person once (latest download)')
                    ->default()
                    ->query(fn (Builder $query) => $query->whereIn('itinerary_downloads.id', ItineraryDownload::query()
                        ->selectRaw('max(id)')->groupBy('phone'))),
            ])
            ->recordActions([
                Action::make('makeEnquiry')
                    ->label('Turn into enquiry')
                    ->icon('heroicon-o-phone-arrow-up-right')
                    ->color('primary')
                    ->visible(fn (ItineraryDownload $record) => ! self::enquiryFor($record)
                        && (bool) auth('admin')->user()?->can('create', Enquiry::class))
                    ->requiresConfirmation()
                    ->modalHeading('Turn this download into an enquiry?')
                    ->modalDescription(fn (ItineraryDownload $record) => $record->name.' will appear under Enquiries → New, so your team calls them about '.($record->package?->title ?? 'this package').'.')
                    ->modalSubmitActionLabel('Create enquiry')
                    ->action(function (ItineraryDownload $record): void {
                        $enquiry = Enquiry::create([
                            'vertical' => 'package',
                            'reference_id' => $record->package_id,
                            'name' => $record->name,
                            'phone' => $record->phone,
                            'email' => $record->email ?: '',
                            'notes' => mb_substr('Downloaded the itinerary for '.($record->package?->title ?? 'a package').' on '.$record->created_at->format('j M Y').'.', 0, 500),
                            'status' => 'new',
                            'source' => 'web',
                        ]);

                        Notification::make()->title('Enquiry created')->body('Find it under Enquiries → New.')->success()
                            ->actions([Action::make('open')->label('Open enquiry')->url(self::enquiryUrl($enquiry))])
                            ->send();
                    }),
                Action::make('openEnquiry')
                    ->label('Open enquiry')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->visible(fn (ItineraryDownload $record) => (bool) self::enquiryFor($record))
                    ->url(fn (ItineraryDownload $record) => self::enquiryUrl(self::enquiryFor($record))),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** The Enquiries list on the enquiry's tab, searched for its phone number. */
    public static function enquiryUrl(Enquiry $enquiry): string
    {
        $tab = match ($enquiry->status) {
            'contacted', 'quoted' => 'in_progress',
            'converted' => 'booked',
            'closed' => 'closed',
            default => 'new',
        };

        return EnquiryResource::getUrl('index', ['tab' => $tab, 'search' => $enquiry->phone]);
    }

    /** @var array<string, Enquiry|null> per request, so each row asks once */
    protected static array $enquiries = [];

    /** An enquiry already made for this person and package, if any. */
    public static function enquiryFor(ItineraryDownload $record): ?Enquiry
    {
        $key = $record->phone.'|'.$record->package_id;
        if (! array_key_exists($key, self::$enquiries) || self::$enquiries[$key] === null) {
            self::$enquiries[$key] = Enquiry::query()
                ->where('phone', $record->phone)
                ->where('vertical', 'package')
                ->where('reference_id', $record->package_id)
                ->latest('id')
                ->first();
        }

        return self::$enquiries[$key];
    }
}
