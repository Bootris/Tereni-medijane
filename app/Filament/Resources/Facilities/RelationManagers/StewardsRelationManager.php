<?php

namespace App\Filament\Resources\Facilities\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'stewards';

    protected static ?string $title = 'Zadužena lica';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Ime i prezime')
                    ->required()
                    ->maxLength(255),
                TextInput::make('role')
                    ->label('Funkcija')
                    ->maxLength(255)
                    ->placeholder('domar, nastavnik, JKP…'),
                TextInput::make('phone')
                    ->label('Telefon')
                    ->tel()
                    ->maxLength(50),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                Toggle::make('notify')
                    ->label('Prima obaveštenja o novim prijavama')
                    ->default(true)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Ime')->searchable(),
                TextColumn::make('role')->label('Funkcija')->badge()->color('gray'),
                TextColumn::make('phone')->label('Telefon'),
                TextColumn::make('email')->label('Email')->icon('heroicon-o-envelope'),
                IconColumn::make('notify')->label('Obaveštenja')->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
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
