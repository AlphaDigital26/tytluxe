<?php

namespace App\Filament\Resources\Admins\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class AdminForm
{
    /** In plain words — keep in step with app/Policies. */
    public const ROLE_HELP = [
        'Super Admin' => 'Everything, including staff accounts, prices, website settings and deleting things.',
        'Operations' => 'Runs bookings: cancel and refund, guest contact details, enquiries. Can also edit hotels, packages, destinations, offers and cruises.',
        'Support' => 'Helps guests: sees bookings, adds notes, fixes contact details, logs and picks up enquiries, approves reviews. Cannot cancel or refund.',
        'Finance' => 'Sees bookings and payments and records refunds made in Razorpay. Cannot cancel bookings.',
        'Content' => 'Looks after the website: packages, hotels, destinations, offers, cruises, blog and reviews. Cannot see bookings or customers.',
        'Analyst' => 'Sees the dashboard, enquiries and website content to report on them. Cannot see bookings or customers.',
    ];

    public const STATUS_HELP = [
        'Active' => 'Can log in.',
        'Suspended' => 'Cannot log in for now (e.g. on leave). Set back to Active at any time.',
        'Blocked' => 'Cannot log in — for someone who has left. Their past work stays on record.',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255),
                Select::make('role')
                    ->options([
                        'Super Admin' => 'Super Admin',
                        'Operations' => 'Operations',
                        'Support' => 'Support',
                        'Finance' => 'Finance',
                        'Content' => 'Content',
                        'Analyst' => 'Analyst',
                    ])
                    ->required()
                    ->default('Operations')
                    ->live()
                    ->helperText(fn (Get $get) => self::ROLE_HELP[$get('role')] ?? 'Choose what this person is allowed to do.')
                    ->disabled(fn (?\Illuminate\Database\Eloquent\Model $record) => $record && $record->id === auth('admin')->id()),
                Select::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Suspended' => 'Suspended',
                        'Blocked' => 'Blocked',
                    ])
                    ->required()
                    ->default('Active')
                    ->live()
                    ->helperText(fn (Get $get) => self::STATUS_HELP[$get('status')] ?? null)
                    ->disabled(fn (?\Illuminate\Database\Eloquent\Model $record) => $record && $record->id === auth('admin')->id()),
                Placeholder::make('roles_explained')
                    ->label('What each role can do')
                    ->columnSpanFull()
                    ->content(new HtmlString(collect(self::ROLE_HELP)
                        ->map(fn (string $help, string $role) => '<div style="padding:3px 0;"><strong>'.e($role).'</strong> — '.e($help).'</div>')
                        ->implode(''))),
            ]);
    }
}
