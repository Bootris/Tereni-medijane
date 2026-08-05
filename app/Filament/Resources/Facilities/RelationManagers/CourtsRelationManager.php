<?php

namespace App\Filament\Resources\Facilities\RelationManagers;

use App\Enums\CourtType;
use App\Filament\Resources\Courts\Schemas\CourtForm;
use App\Models\Court;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CourtsRelationManager extends RelationManager
{
    protected static string $relationship = 'courts';

    protected static ?string $title = 'Tereni';

    public function form(Schema $schema): Schema
    {
        // Facility is implied by the relationship — hide the picker.
        return CourtForm::configure($schema, withFacility: false);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Teren')->searchable(),
                TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn (CourtType $state): string => $state->label()),
                IconColumn::make('has_lighting')->label('Osvetljenje')->boolean(),
                TextColumn::make('reports_count')->label('Prijave')->counts('reports')->badge()->color('warning'),
                IconColumn::make('is_active')->label('Aktivan')->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('qr')
                    ->label('QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('gray')
                    ->url(fn (Court $record): string => route('tereni.court.qr', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
