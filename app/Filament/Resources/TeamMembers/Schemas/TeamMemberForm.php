<?php

namespace App\Filament\Resources\TeamMembers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Profile')
                    ->columnSpan(2)
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
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
                            ->unique(ignoreRecord: true),
                        TextInput::make('title')
                            ->label('Position')
                            ->maxLength(255)
                            ->helperText('E.g. Senior Partner, Corporate Law'),
                        RichEditor::make('bio')
                            ->label('Biography')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('team/attachments'),
                    ]),

                Section::make('Photo & contact')
                    ->columnSpan(1)
                    ->components([
                        FileUpload::make('photo')
                            ->image()
                            ->disk('public')
                            ->directory('team')
                            ->imageEditor()
                            ->avatar()
                            ->maxSize(4096),
                        TextInput::make('email')->email()->maxLength(255),
                        TextInput::make('phone')->tel()->maxLength(255),
                        TextInput::make('linkedin')->url()->maxLength(255),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first.'),
                        Toggle::make('visible')
                            ->label('Show on website')
                            ->default(true),
                    ]),
            ]);
    }
}
