<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\Pages\EditReport;
use App\Filament\Resources\Reports\Pages\ListReports;
use App\Filament\Resources\Reports\Schemas\ReportForm;
use App\Filament\Resources\Reports\Tables\ReportsTable;
use App\Models\Report;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|\UnitEnum|null $navigationGroup = 'Tereni Medijana';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Prijave';

    protected static ?string $modelLabel = 'prijava';

    protected static ?string $pluralModelLabel = 'prijave';

    protected static ?string $recordTitleAttribute = 'id';

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) config('site.features.tereni');
    }

    /** Badge: reports awaiting moderation. */
    public static function getNavigationBadge(): ?string
    {
        if (! config('site.features.tereni')) {
            return null;
        }

        $count = Report::where('is_public', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return ReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReports::route('/'),
            'edit' => EditReport::route('/{record}/edit'),
        ];
    }
}
