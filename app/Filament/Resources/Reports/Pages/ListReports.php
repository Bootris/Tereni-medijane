<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use Filament\Resources\Pages\ListRecords;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    // No "create" — reports come from citizens via the public form, not the admin.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
