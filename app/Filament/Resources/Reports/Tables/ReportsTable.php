<?php

namespace App\Filament\Resources\Reports\Tables;

use App\Enums\ReportCategory;
use App\Enums\ReportStatus;
use App\Models\Report;
use App\Support\Tereni\ReportNotifier;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('photo')
                    ->label('')
                    ->disk('public')
                    ->square(),
                TextColumn::make('court.name')
                    ->label('Teren')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Report $record): ?string => $record->court?->facility?->name),
                TextColumn::make('category')
                    ->label('Kategorija')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (ReportCategory $state): string => $state->label()),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (ReportStatus $state): string => $state->color())
                    ->formatStateUsing(fn (ReportStatus $state): string => $state->label()),
                IconColumn::make('is_public')
                    ->label('Javno')
                    ->boolean(),
                IconColumn::make('is_flagged')
                    ->label('Flag')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray'),
                TextColumn::make('created_at')
                    ->label('Prijavljeno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ReportStatus::options()),
                SelectFilter::make('category')
                    ->label('Kategorija')
                    ->options(ReportCategory::options()),
                SelectFilter::make('court')
                    ->label('Teren')
                    ->relationship('court', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_public')
                    ->label('Javno vidljivo'),
                TernaryFilter::make('is_flagged')
                    ->label('Prijavljeno kao neprikladno'),
            ])
            ->recordActions([
                Action::make('publish')
                    ->label('Objavi')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn (Report $record): bool => ! $record->is_public)
                    ->requiresConfirmation()
                    ->action(fn (Report $record) => $record->publish(Auth::user())),
                Action::make('changeStatus')
                    ->label('Promeni status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->schema([
                        Select::make('status')
                            ->label('Novi status')
                            ->options(ReportStatus::options())
                            ->default(fn (Report $record): string => $record->status->value)
                            ->required()
                            ->native(false),
                        Textarea::make('note')
                            ->label('Javni komentar (opciono)')
                            ->rows(2),
                    ])
                    ->action(function (Report $record, array $data, ReportNotifier $notifier): void {
                        $record->changeStatus(
                            ReportStatus::from($data['status']),
                            $data['note'] ?? null,
                            Auth::user(),
                        );
                        $notifier->statusChanged($record);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
