<?php

namespace App\Filament\Resources\Admins\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class AdminForm
{
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
                    ->disabled(fn (?\Illuminate\Database\Eloquent\Model $record) => $record && $record->id === auth('admin')->id()),
                Select::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Suspended' => 'Suspended',
                        'Blocked' => 'Blocked',
                    ])
                    ->required()
                    ->default('Active')
                    ->disabled(fn (?\Illuminate\Database\Eloquent\Model $record) => $record && $record->id === auth('admin')->id()),
            ]);
    }
}
