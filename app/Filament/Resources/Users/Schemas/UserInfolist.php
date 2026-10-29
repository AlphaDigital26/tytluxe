<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * The customer page, read only: customers keep their own details up to date
 * on the website, so staff see plain text rather than greyed-out form boxes.
 * Anything the customer hasn't filled in is left out, and a section with
 * nothing in it is hidden.
 */
class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Contact')->columns(2)->schema([
                self::entry('name', 'Name')->icon('heroicon-m-user'),
                self::entry('email', 'Email')->icon('heroicon-m-envelope')->copyable(),
                self::entry('phone', 'Mobile number')->icon('heroicon-m-phone')->copyable(),
            ]),

            Section::make('Account')->columns(3)->schema([
                TextEntry::make('signed_up_with')->label('Signs in with')
                    ->state(fn (User $record) => $record->google_id ? 'Google' : 'Email code'),
                TextEntry::make('status')->label('Account')
                    ->badge()->color(fn (?string $state) => $state === 'Active' ? 'success' : 'danger'),
                TextEntry::make('email_check')->label('Email confirmed')
                    ->state(fn (User $record) => $record->email_verified_at ? 'Yes' : 'Not yet'),
                TextEntry::make('created_at')->label('Joined')->dateTime('j M Y, g:i A'),
                self::entry('last_login_at', 'Last signed in')->dateTime('j M Y, g:i A'),
            ]),

            self::section('Personal details', 3, [
                self::entry('dob', 'Date of birth')->date('j M Y'),
                self::entry('gender', 'Gender'),
                self::entry('nationality', 'Nationality'),
                self::entry('marital_status', 'Marital status'),
                self::entry('anniversary', 'Anniversary')->date('j M Y'),
            ], ['dob', 'gender', 'nationality', 'marital_status', 'anniversary']),

            self::section('Address', 2, [
                self::entry('address.line1', 'Address')->columnSpanFull(),
                self::entry('address.city', 'City'),
                self::entry('address.state', 'State'),
                self::entry('address.pin', 'PIN code'),
                self::entry('address.country', 'Country'),
            ], ['address']),

            self::section('Travel preferences', 3, [
                self::entry('preferences.hotel_room', 'Hotel room'),
                self::entry('preferences.flight', 'Flights'),
                self::entry('preferences.meal', 'Meals'),
                self::entry('preferences.currency', 'Currency'),
                self::entry('preferences.language', 'Language'),
            ], ['preferences']),

            self::section('Travel documents', 3, [
                self::entry('passport_no', 'Passport number'),
                self::entry('passport_expiry', 'Passport expiry')->date('j M Y'),
                self::entry('passport_issuing_country', 'Issued by'),
                self::entry('id_list', 'Other ID')->columnSpanFull()
                    ->state(fn (User $record) => collect($record->govt_ids ?? [])
                        ->filter(fn ($id) => filled($id['number'] ?? null))
                        ->map(fn ($id) => trim(($id['type'] ?? 'ID').': '.$id['number']))
                        ->implode(' · ') ?: null),
            ], ['passport_no', 'passport_expiry', 'passport_issuing_country', 'govt_ids']),
        ]);
    }

    /** A detail that's only shown when the customer filled it in. */
    private static function entry(string $field, string $label): TextEntry
    {
        return TextEntry::make($field)->label($label)->hidden(fn ($state) => blank($state));
    }

    /** A section that's hidden when every field behind it is empty. */
    private static function section(string $title, int $columns, array $entries, array $fields): Section
    {
        return Section::make($title)->columns($columns)->schema($entries)
            ->hidden(fn (?User $record) => $record === null
                || collect($fields)->every(fn ($f) => blank(array_filter((array) data_get($record, $f)))));
    }
}
