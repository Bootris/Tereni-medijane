<?php

namespace App\Filament\Resources\Courts\Schemas;

use App\Enums\CourtAccess;
use App\Enums\CourtType;
use App\Models\Court;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CourtForm
{
    /**
     * @param  bool  $withFacility  show the facility picker (standalone resource)
     *                              vs. hide it (inside a facility relation manager).
     */
    public static function configure(Schema $schema, bool $withFacility = true): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Teren')
                    ->columnSpan(2)
                    ->columns(2)
                    ->components([
                        ...$withFacility ? [
                            Select::make('facility_id')
                                ->label('Objekat')
                                ->relationship('facility', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->native(false)
                                ->columnSpanFull(),
                        ] : [],
                        TextInput::make('name')
                            ->label('Naziv terena')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, callable $set) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('QR slug')
                            ->required()
                            ->maxLength(255)
                            ->rules(['alpha_dash'])
                            ->unique(Court::class, 'slug', ignoreRecord: true)
                            ->helperText('Adresa iza QR koda: /teren/{slug}'),
                        Select::make('type')
                            ->label('Tip')
                            ->options(CourtType::options())
                            ->required()
                            ->native(false),
                        Select::make('access')
                            ->label('Dostupnost')
                            ->options(CourtAccess::options())
                            ->default(CourtAccess::Public->value)
                            ->required()
                            ->native(false),
                        TextInput::make('surface')
                            ->label('Podloga')
                            ->maxLength(255)
                            ->placeholder('beton, tartan, veštačka trava…'),
                        TextInput::make('dimensions')
                            ->label('Dimenzije')
                            ->maxLength(255)
                            ->placeholder('28 × 15 m'),
                        Toggle::make('has_lighting')
                            ->label('Ima osvetljenje'),
                        Toggle::make('is_active')
                            ->label('Aktivan (vidljiv na mapi)')
                            ->default(true),
                        Textarea::make('description')
                            ->label('Opis')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Lokacija i galerija')
                    ->columnSpan(1)
                    ->components([
                        TextInput::make('lat')
                            ->label('Geo širina (lat)')
                            ->numeric()
                            ->helperText('Ako je prazno, koristi se lokacija objekta.'),
                        TextInput::make('lng')
                            ->label('Geo dužina (lng)')
                            ->numeric(),
                        FileUpload::make('gallery')
                            ->label('Foto galerija')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->downloadable()
                            ->disk('public')
                            ->directory('tereni/courts')
                            ->maxSize(8192)
                            ->maxFiles(12)
                            // Browser downscales before upload: keeps requests under
                            // server body-size limits and disk usage ~10x lower.
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1920')
                            ->imageResizeUpscale(false)
                            // Removed images are deleted from disk on save (model hook).
                            ->helperText('Do 12 slika, najviše 8 MB po slici. Prevuci sličice za redosled prikaza; X uklanja sliku (briše se sa diska pri čuvanju).'),
                    ]),
            ]);
    }
}
