<?php

namespace App\Filament\Exports;

use App\Models\Enquiry;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class EnquiryExporter extends Exporter
{
    protected static ?string $model = Enquiry::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('user_id'),
            ExportColumn::make('vertical'),
            ExportColumn::make('reference_id'),
            ExportColumn::make('name'),
            ExportColumn::make('phone'),
            ExportColumn::make('email'),
            ExportColumn::make('travel_date_from'),
            ExportColumn::make('travel_date_to'),
            ExportColumn::make('pax_adults'),
            ExportColumn::make('pax_children'),
            ExportColumn::make('notes'),
            ExportColumn::make('admin_notes'),
            ExportColumn::make('status'),
            ExportColumn::make('assigned_agent_id'),
            ExportColumn::make('source'),
            ExportColumn::make('resolved_at'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your enquiry export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
