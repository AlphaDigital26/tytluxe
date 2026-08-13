<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('ProfileDetails')
                    ->tabs([
                        Tab::make('Basic Information')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('user_id')
                                        ->label('User ID')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->visibleOn('view'),
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
                                    TextInput::make('nationality')
                                        ->label('Nationality'),
                                    Select::make('marital_status')
                                        ->label('Marital Status')
                                        ->options([
                                            'Single' => 'Single',
                                            'Married' => 'Married',
                                        ]),
                                    \Filament\Forms\Components\DatePicker::make('anniversary')
                                        ->label('Anniversary'),
                                ]),
                            ]),

                        Tab::make('Address & Preferences')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('address.country')->label('Country'),
                                    TextInput::make('address.state')->label('State'),
                                    TextInput::make('address.city')->label('City'),
                                    TextInput::make('address.pin')->label('PIN/Postal Code'),
                                    Textarea::make('address.line1')->label('Address')->columnSpanFull(),
                                    
                                    TextInput::make('preferences.currency')->label('Preferred Currency'),
                                    TextInput::make('preferences.language')->label('Preferred Language'),
                                    TextInput::make('preferences.hotel_room')->label('Hotel Room Preference'),
                                    TextInput::make('preferences.flight')->label('Flight Preference'),
                                    TextInput::make('preferences.meal')->label('Meal Preference'),
                                ]),
                            ]),

                        Tab::make('Travel Documents')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('passport_no')->label('Passport Number'),
                                    \Filament\Forms\Components\DatePicker::make('passport_expiry')->label('Passport Expiry'),
                                    TextInput::make('passport_issuing_country')->label('Issuing Country'),
                                ]),
                                Repeater::make('govt_ids')
                                    ->label('Government IDs')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Select::make('type')->options([
                                                'Aadhaar Card' => 'Aadhaar Card',
                                                'PAN Card' => 'PAN Card',
                                                'Driving License' => 'Driving License',
                                            ]),
                                            TextInput::make('number'),
                                        ]),
                                    ])
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('System Info')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('registration_method')
                                        ->label('Registration Method')
                                        ->formatStateUsing(fn ($record) => $record?->google_id ? 'Google' : 'Email (OTP)')
                                        ->disabled()
                                        ->dehydrated(false),
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
                                    DateTimePicker::make('created_at')->disabled()->dehydrated(false),
                                    DateTimePicker::make('updated_at')->disabled()->dehydrated(false),
                                    DateTimePicker::make('last_login_at')->disabled()->dehydrated(false),
                                ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
