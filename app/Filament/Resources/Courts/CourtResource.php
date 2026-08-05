<?php

namespace App\Filament\Resources\Courts;

use App\Filament\Resources\Courts\Pages\CreateCourt;
use App\Filament\Resources\Courts\Pages\EditCourt;
use App\Filament\Resources\Courts\Pages\ListCourts;
use App\Filament\Resources\Courts\Schemas\CourtForm;
use App\Filament\Resources\Courts\Tables\CourtsTable;
use App\Models\Court;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CourtResource extends Resource
{
    protected static ?string $model = Court::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|\UnitEnum|null $navigationGroup = 'Tereni Medijana';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Tereni';

    protected static ?string $modelLabel = 'teren';

    protected static ?string $pluralModelLabel = 'tereni';

    protected static ?string $recordTitleAttribute = 'name';

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) config('site.features.tereni');
    }

    public static function form(Schema $schema): Schema
    {
        return CourtForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourtsTable::configure($table);
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
            'index' => ListCourts::route('/'),
            'create' => CreateCourt::route('/create'),
            'edit' => EditCourt::route('/{record}/edit'),
        ];
    }
}
