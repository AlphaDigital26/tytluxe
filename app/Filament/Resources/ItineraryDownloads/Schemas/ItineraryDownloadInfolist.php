<?php

namespace App\Filament\Resources\ItineraryDownloads\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ItineraryDownloadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ])->columns(2),

                Section::make('Download Details')
                    ->schema([
                        TextEntry::make('package.title')
                            ->label('Package')
                            ->icon('heroicon-m-briefcase')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label('Downloaded On')
                            ->icon('heroicon-m-clock')
                            ->dateTime('M j, Y h:i A'),
                    ])->columns(2),
            ]);
    }
}
