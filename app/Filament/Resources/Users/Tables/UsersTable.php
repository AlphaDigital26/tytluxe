<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\User;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('gender')
                    ->badge()
                    ->color('success'),
                IconColumn::make('newsletter')
                    ->label('Newsletter')
                    ->boolean()
                    ->getStateUsing(fn (User $record) => $record->preferences['newsletter'] ?? false),
                TextColumn::make('registration_method')
                    ->label('Registration')
                    ->getStateUsing(fn (User $record) => $record->google_id ? 'Google' : 'Email (OTP)')
                    ->badge()
                    ->color(fn ($state) => $state === 'Google' ? 'danger' : 'info'),
                TextColumn::make('verification')
                    ->label('Verification')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(fn (User $record) => $record->email_verified_at ? 'Verified' : 'Unverified'),
                TextColumn::make('created_at')
                    ->label('Registered On')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->getStateUsing(fn (User $record) => $record->status === 'Active'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
                Action::make('suspend')
                    ->label('Suspend User')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->update(['status' => 'Suspended']))
                    ->visible(fn (User $record) => $record->status === 'Active'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
