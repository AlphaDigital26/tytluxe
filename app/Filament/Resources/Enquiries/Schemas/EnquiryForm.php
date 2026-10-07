<?php

namespace App\Filament\Resources\Enquiries\Schemas;

use App\Filament\Resources\Enquiries\Tables\EnquiriesTable;
use App\Models\Admin;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * For enquiries taken by phone/WhatsApp and typed in by staff. user_id and
 * reference_id (set automatically for website enquiries) are never typed
 * by hand, so they aren't on the form.
 */
class EnquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Guest')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')->label('Guest name')->required(),
                        TextInput::make('phone')->label('Phone')->tel()->required(),
                        TextInput::make('email')->label('Email')->email()->required(),
                    ]),
                Section::make('Trip')
                    ->columns(3)
                    ->schema([
                        Select::make('vertical')
                            ->label('Looking for')
                            ->options(EnquiriesTable::CATEGORIES)
                            ->default('general')
                            ->required(),
                        DatePicker::make('travel_date_from')->label('Travel from'),
                        DatePicker::make('travel_date_to')->label('Travel until'),
                        TextInput::make('pax_adults')->label('Adults')->numeric()->minValue(1)->default(1)->required(),
                        TextInput::make('pax_children')->label('Children')->numeric()->minValue(0)->default(0)->required(),
                        Textarea::make('notes')->label('What the guest asked for')->rows(3)->columnSpanFull(),
                    ]),
                Section::make('Follow-up')
                    ->columns(3)
                    ->schema([
                        Select::make('status')
                            ->label('Stage')
                            ->options(EnquiriesTable::STATUSES)
                            ->default('new')
                            ->required(),
                        Select::make('assigned_agent_id')
                            ->label('Handled by')
                            ->options(fn () => Admin::where('status', 'Active')->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable(),
                        Select::make('source')
                            ->label('Came in via')
                            ->options(['web' => 'Website', 'whatsapp' => 'WhatsApp', 'phone' => 'Phone call'])
                            ->default('phone')
                            ->required(),
                    ]),
            ]);
    }
}
