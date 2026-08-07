<?php

namespace App\Filament\Resources\Admins\Pages;

use App\Filament\Resources\Admins\AdminResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('changePassword')
                ->label('Change Password')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->form([
                    TextInput::make('current_password')
                        ->password()
                        ->required()
                        ->label('Current Password')
                        ->currentPassword(),
                    TextInput::make('new_password')
                        ->password()
                        ->required()
                        ->label('New Password')
                        ->rule(Password::default()),
                    TextInput::make('new_password_confirmation')
                        ->password()
                        ->required()
                        ->label('Confirm New Password')
                        ->same('new_password'),
                ])
                ->action(function (array $data, $record) {
                    $record->update([
                        'password' => Hash::make($data['new_password']),
                    ]);

                    auth('admin')->logout();
                    request()->session()->invalidate();
                    request()->session()->regenerateToken();

                    return redirect()->route('filament.admin.auth.login');
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
