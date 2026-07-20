<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('read_at')
                    ->label('')
                    ->badge()
                    ->state(fn ($record): string => $record->read_at ? 'Read' : 'New')
                    ->color(fn (string $state): string => $state === 'New' ? 'warning' : 'gray'),
                TextColumn::make('name')
                    ->searchable()
                    ->weight(fn ($record) => $record->read_at ? null : FontWeight::Bold),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('subject')
                    ->searchable()
                    ->limit(50)
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('read_at')
                    ->label('Read')
                    ->nullable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
