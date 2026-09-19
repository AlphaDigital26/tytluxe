<?php

namespace App\Filament\Resources\Enquiries;

use App\Filament\Resources\Enquiries\Pages\CreateEnquiry;
use App\Filament\Resources\Enquiries\Pages\EditEnquiry;
use App\Filament\Resources\Enquiries\Pages\ListEnquiries;
use App\Filament\Resources\Enquiries\Schemas\EnquiryForm;
use App\Filament\Resources\Enquiries\Tables\EnquiriesTable;
use App\Models\Enquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class EnquiryResource extends Resource
{
    protected static ?string $model = Enquiry::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EnquiryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnquiriesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Guest Details')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name')
                            ->icon('heroicon-m-user'),
                        TextEntry::make('phone')
                            ->label('Phone Number')
                            ->icon('heroicon-m-phone'),
                        TextEntry::make('email')
                            ->label('Email Address')
                            ->icon('heroicon-m-envelope')
                            ->columnSpanFull(),
                        TextEntry::make('vertical')
                            ->label('Category')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('reference_id')
                            ->label(fn ($record) => ucfirst($record->vertical ?: 'Reference'))
                            ->icon('heroicon-m-map-pin')
                            ->getStateUsing(fn ($record) => $record->verticalModel()?->first()?->title)
                            ->visible(fn ($record) => (bool) $record->verticalModel()?->first()),
                    ])->columns(2),

                Section::make('Enquiry Details')
                    ->schema([
                        TextEntry::make('travel_date_from')
                            ->label('Check-in / Start')
                            ->date()
                            ->icon('heroicon-m-calendar')
                            ->visible(fn ($record) => !empty($record->travel_date_from)),
                        TextEntry::make('travel_date_to')
                            ->label('Check-out / End')
                            ->date()
                            ->icon('heroicon-m-calendar')
                            ->visible(fn ($record) => !empty($record->travel_date_to)),
                        TextEntry::make('nights')
                            ->label('Duration (Nights)')
                            ->icon('heroicon-m-moon')
                            ->getStateUsing(function ($record) {
                                if ($record->travel_date_from && $record->travel_date_to) {
                                    $from = \Carbon\Carbon::parse($record->travel_date_from);
                                    $to = \Carbon\Carbon::parse($record->travel_date_to);
                                    return $from->diffInDays($to);
                                }
                                return '-';
                            })
                            ->visible(fn ($record) => !empty($record->travel_date_from) && !empty($record->travel_date_to)),
                        TextEntry::make('guests')
                            ->label('Guests')
                            ->icon('heroicon-m-users')
                            ->getStateUsing(function ($record) {
                                $adults = $record->pax_adults ?: 0;
                                $children = $record->pax_children ?: 0;
                                return "{$adults} Adults, {$children} Children";
                            })
                            ->visible(fn ($record) => in_array($record->vertical, ['hotel', 'package', 'staycation'])),
                        TextEntry::make('room_breakdown')
                            ->label('Rooms & Guests')
                            ->icon('heroicon-m-user-group')
                            ->columnSpanFull()
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->getStateUsing(fn ($record) => static::parseRoomLines($record->notes))
                            ->visible(fn ($record) => !empty(static::parseRoomLines($record->notes))),
                        TextEntry::make('additional_requirements')
                            ->label('Additional Requirements')
                            ->icon('heroicon-m-chat-bubble-left-right')
                            ->columnSpanFull()
                            ->formatStateUsing(fn ($state) => nl2br(e($state)))
                            ->html()
                            ->getStateUsing(fn ($record) => static::parseRemainingNotes($record->notes))
                            ->visible(fn ($record) => filled(static::parseRemainingNotes($record->notes))),
                        TextEntry::make('no_requirements')
                            ->label('Additional Requirements')
                            ->columnSpanFull()
                            ->getStateUsing(fn () => 'No additional requirements provided.')
                            ->color('gray')
                            ->visible(fn ($record) => empty(static::parseRoomLines($record->notes)) && !filled(static::parseRemainingNotes($record->notes))),
                    ])->columns(2),

                Section::make('Resolution Details')
                    ->schema([
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('assignedAgent.name')
                            ->label('Resolved By')
                            ->icon('heroicon-m-user-circle')
                            ->default('-'),
                        TextEntry::make('resolved_at')
                            ->label('Resolved On')
                            ->dateTime('M j, Y h:i A')
                            ->icon('heroicon-m-clock')
                            ->default('-'),
                        TextEntry::make('admin_notes')
                            ->label('Resolution Comments')
                            ->columnSpanFull()
                            ->default('-'),
                    ])->columns(2)
                    ->visible(fn ($record) => $record->status === 'closed'),
            ]);
    }

    /**
     * Pull the "Rooms: ..." block out of the notes field as one line per room,
     * e.g. ["Room 1: 2 Adults", "Room 2: 1 Adult, 1 Child (5 yrs)"].
     */
    protected static function parseRoomLines(?string $notes): array
    {
        if (empty($notes) || !preg_match('/Rooms:\s*(.+?)(?:\n\n|$)/s', $notes, $m)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode("\n", trim($m[1])))));
    }

    /**
     * Everything in notes that isn't the "Rooms: ..." block — the guest's
     * own free-text message, or a legacy note with no room breakdown.
     */
    protected static function parseRemainingNotes(?string $notes): ?string
    {
        if (empty($notes)) {
            return null;
        }

        $remaining = trim(preg_replace('/Rooms:\s*.+?(?=\n\n|$)/s', '', $notes));

        return $remaining !== '' ? $remaining : null;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnquiries::route('/'),
            'create' => CreateEnquiry::route('/create'),
        ];
    }
}

