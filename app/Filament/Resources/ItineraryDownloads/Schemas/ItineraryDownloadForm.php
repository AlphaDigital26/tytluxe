<?php

namespace App\Filament\Resources\ItineraryDownloads\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ItineraryDownloadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('package_id')
                    ->relationship('package', 'title')
                    ->label('Package')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
            ]);
    }
}
