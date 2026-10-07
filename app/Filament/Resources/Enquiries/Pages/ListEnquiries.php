<?php

namespace App\Filament\Resources\Enquiries\Pages;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Models\Enquiry;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEnquiries extends ListRecords
{
    protected static string $resource = EnquiryResource::class;

    public function getSubheading(): ?string
    {
        return 'Leads from the website, WhatsApp and phone. Use "Update stage" as you follow up, and "Close" once it\'s done.';
    }

    protected function getHeaderActions(): array
    {
        return [
            \pxlrbt\FilamentExcel\Actions\ExportAction::make()
                ->label('Download as Excel')
                ->exports([
                    \pxlrbt\FilamentExcel\Exports\ExcelExport::make()->fromModel(),
                ]),
            CreateAction::make()->label('Add enquiry (phone / WhatsApp)'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'new' => Tab::make('New')
                ->badge(Enquiry::where('status', 'new')->count() ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'new')),
            'in_progress' => Tab::make('Following up')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['contacted', 'quoted'])),
            'booked' => Tab::make('Booked')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'converted')),
            'closed' => Tab::make('Closed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'closed')),
            'all' => Tab::make('All'),
        ];
    }
}
