<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Password;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    // One clear Save button — no "Create & create another".
    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16));
        return $data;
    }

    /**
     * Staff added this customer by their email, so count it as verified —
     * otherwise the hourly clean-up removes unverified sign-ups after 48
     * hours. Then, if ticked, email them a link to set their own password
     * (the website's normal "reset password" email).
     */
    protected function afterCreate(): void
    {
        $this->record->forceFill(['email_verified_at' => now()])->save();

        if (! ($this->data['send_password_link'] ?? false)) {
            return;
        }

        $status = Password::broker('users')->sendResetLink(['email' => $this->record->email]);

        $status === Password::RESET_LINK_SENT
            ? Notification::make()->title('Password link sent')->body('We emailed '.$this->record->email.' a link to set their password. It works for 60 minutes; they can ask for a new one with "Forgot password".')->success()->send()
            : Notification::make()->title('Password link not sent')->body('The customer was added, but the email could not be sent. They can use "Forgot password" on the website.')->warning()->send();
    }
}
