<?php

namespace App\Filament\Resources\Courts\Tables;

use App\Enums\CourtAccess;
use App\Enums\CourtType;
use App\Models\Court;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CourtsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Teren')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Court $record): ?string => $record->facility?->name),
                TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn (CourtType $state): string => $state->label()),
                TextColumn::make('access')
                    ->label('Dostupnost')
                    ->badge()
                    ->color(fn (CourtAccess $state): string => $state->color())
                    ->formatStateUsing(fn (CourtAccess $state): string => $state->label()),
                IconColumn::make('has_lighting')
                    ->label('Osvetljenje')
                    ->boolean(),
                TextColumn::make('reports_count')
                    ->label('Prijave')
                    ->counts('reports')
                    ->badge()
                    ->color('warning'),
                IconColumn::make('is_active')
                    ->label('Aktivan')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('facility')
                    ->label('Objekat')
                    ->relationship('facility', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label('Tip')
                    ->options(CourtType::options()),
                TernaryFilter::make('is_active')
                    ->label('Aktivan'),
            ])
            ->recordActions([
                Action::make('qr')
                    ->label('QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('gray')
                    ->url(fn (Court $record): string => route('tereni.court.qr', $record))
                    ->openUrlInNewTab(),
                Action::make('view')
                    ->label('Javna strana')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Court $record): string => route('tereni.court', $record))
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
