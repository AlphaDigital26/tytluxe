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
use Filament\Infolists\Components\KeyValueEntry;
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
                        KeyValueEntry::make('notes')
                            ->label('Requirement Details')
                            ->columnSpanFull()
                            ->keyLabel('Field')
                            ->valueLabel('Detail')
                            ->getStateUsing(function ($record) {
                                $state = $record->notes;
                                if (empty($state)) return [];
                                if (str_contains($state, 'Guests: [')) {
                                    $state = preg_replace('/Guests:\s*\[.*\]/s', '', $state);
                                }
                                $state = trim($state);
                                
                                if (str_contains($state, "\n")) {
                                    $lines = explode("\n", $state);
                                    $array = [];
                                    foreach ($lines as $line) {
                                        $line = trim($line);
                                        if (empty($line)) continue;
                                        if (str_contains($line, ':')) {
                                            [$key, $value] = explode(':', $line, 2);
                                            $array[trim($key)] = trim($value);
                                        } else {
                                            $array['Note'] = $line;
                                        }
                                    }
                                    return $array;
                                }
                                
                                return ['Details' => $state];
                            }),
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
                            ->dateTime()
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

