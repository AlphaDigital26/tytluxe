<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->label('User ID')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('name')
                    ->label('Full Name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->label('Mobile Number')
                    ->tel(),
                \Filament\Forms\Components\DatePicker::make('dob')
                    ->label('Date of Birth'),
                Select::make('gender')
                    ->label('Gender')
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                        'Other' => 'Other',
                    ]),
                TextInput::make('address.country')
                    ->label('Country'),
                TextInput::make('address.state')
                    ->label('State'),
                TextInput::make('address.city')
                    ->label('City'),
                \Filament\Forms\Components\Textarea::make('address.line1')
                    ->label('Address'),
                TextInput::make('address.pin')
                    ->label('PIN/Postal Code'),
                Toggle::make('is_email_verified')
                    ->label('Email Verified')
                    ->statePath('email_verified_at')
                    ->formatStateUsing(fn ($state) => filled($state))
                    ->disabled()
                    ->dehydrated(false),
                Toggle::make('is_phone_verified')
                    ->label('Phone Verified')
                    ->statePath('phone_verified_at')
                    ->formatStateUsing(fn ($state) => filled($state))
                    ->disabled()
                    ->dehydrated(false),
                Select::make('status')
                    ->label('Account Status')
                    ->options([
                        'Active' => 'Active',
                        'Suspended' => 'Suspended',
                        'Blocked' => 'Blocked',
                    ])
                    ->required()
                    ->default('Active'),
                DateTimePicker::make('created_at')
                    ->label('Created At')
                    ->disabled()
                    ->dehydrated(false),
                DateTimePicker::make('updated_at')
                    ->label('Updated At')
                    ->disabled()
                    ->dehydrated(false),
                DateTimePicker::make('last_login_at')
                    ->label('Last Login')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }
}
