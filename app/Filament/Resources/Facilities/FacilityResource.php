<?php

namespace App\Filament\Resources\Facilities;

use App\Filament\Resources\Facilities\Pages\CreateFacility;
use App\Filament\Resources\Facilities\Pages\EditFacility;
use App\Filament\Resources\Facilities\Pages\ListFacilities;
use App\Filament\Resources\Facilities\RelationManagers\CourtsRelationManager;
use App\Filament\Resources\Facilities\RelationManagers\StewardsRelationManager;
use App\Filament\Resources\Facilities\Schemas\FacilityForm;
use App\Filament\Resources\Facilities\Tables\FacilitiesTable;
use App\Models\Facility;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FacilityResource extends Resource
{
    protected static ?string $model = Facility::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|\UnitEnum|null $navigationGroup = 'Tereni Medijana';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Objekti';

    protected static ?string $modelLabel = 'objekat';

    protected static ?string $pluralModelLabel = 'objekti';

    protected static ?string $recordTitleAttribute = 'name';

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) config('site.features.tereni');
    }

    public static function form(Schema $schema): Schema
    {
        return FacilityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FacilitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CourtsRelationManager::class,
            StewardsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFacilities::route('/'),
            'create' => CreateFacility::route('/create'),
            'edit' => EditFacility::route('/{record}/edit'),
        ];
    }
}
