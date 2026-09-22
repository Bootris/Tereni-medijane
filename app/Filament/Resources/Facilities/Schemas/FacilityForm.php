<?php

namespace App\Filament\Resources\Facilities\Schemas;

use App\Filament\Components\MapsLinkInput;
use App\Models\Facility;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class FacilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Objekat')
                    ->columnSpan(2)
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Naziv')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, callable $set) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->rules(['alpha_dash'])
                            ->unique(Facility::class, 'slug', ignoreRecord: true)
                            ->helperText('Deo javne adrese objekta.'),
                        Select::make('ownership')
                            ->label('Vlasništvo')
                            ->options([
                                'skola' => 'Škola',
                                'opstina' => 'Opština',
                                'privatno' => 'Privatno',
                                'ostalo' => 'Ostalo',
                            ])
                            ->native(false),
                        TextInput::make('address')
                            ->label('Adresa')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Napomene')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Lokacija')
                    ->columnSpan(1)
                    ->components([
                        MapsLinkInput::make(),
                        TextInput::make('lat')
                            ->label('Geo širina (lat)')
                            ->numeric()
                            ->helperText('Npr. 43.3192'),
                        TextInput::make('lng')
                            ->label('Geo dužina (lng)')
                            ->numeric()
                            ->helperText('Npr. 21.9161'),
                    ]),
            ]);
    }
}
