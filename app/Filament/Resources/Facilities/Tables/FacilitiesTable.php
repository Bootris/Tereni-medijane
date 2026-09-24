<?php

namespace App\Filament\Resources\Facilities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FacilitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Objekat')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record): ?string => $record->address),
                TextColumn::make('ownership')
                    ->label('Vlasništvo')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'skola' => 'Škola',
                        'opstina' => 'Opština',
                        'privatno' => 'Privatno',
                        default => $state ?: '-',
                    }),
                TextColumn::make('courts_count')
                    ->label('Tereni')
                    ->counts('courts')
                    ->badge(),
                TextColumn::make('stewards_count')
                    ->label('Zaduženi')
                    ->counts('stewards')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                SelectFilter::make('ownership')
                    ->label('Vlasništvo')
                    ->options([
                        'skola' => 'Škola',
                        'opstina' => 'Opština',
                        'privatno' => 'Privatno',
                        'ostalo' => 'Ostalo',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
