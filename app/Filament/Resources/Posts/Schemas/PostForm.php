<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Content')
                    ->columnSpan(2)
                    ->components([
                        TextInput::make('title')
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
                            ->unique(ignoreRecord: true)
                            ->helperText('Used in the article URL, e.g. /blog/my-article'),
                        Textarea::make('excerpt')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Short summary shown in article listings and search results.'),
                        RichEditor::make('body')
                            ->required()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('posts/attachments'),
                    ]),

                Section::make('Publishing')
                    ->columnSpan(1)
                    ->components([
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->required()
                            ->native(false)
                            ->live(),
                        DateTimePicker::make('published_at')
                            ->seconds(false)
                            ->default(now())
                            ->helperText('Future date = scheduled; the article appears once the time passes.'),
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->createOptionForm([
                                TextInput::make('name')->required()->maxLength(255),
                            ])
                            ->native(false),
                        Toggle::make('show_on_home')
                            ->label('Feature on homepage'),
                    ]),

                Section::make('Media')
                    ->columnSpan(1)
                    ->components([
                        FileUpload::make('featured_image')
                            ->image()
                            ->disk('public')
                            ->directory('posts')
                            ->imageEditor()
                            ->maxSize(4096)
                            ->helperText('Shown at the top of the article and on cards.'),
                        TextInput::make('video_url')
                            ->label('Video URL')
                            ->url()
                            ->helperText('YouTube or Vimeo link — embedded in the article.'),
                    ]),

                Section::make('SEO')
                    ->columnSpan(1)
                    ->collapsed()
                    ->components([
                        TextInput::make('seo_title')
                            ->maxLength(255)
                            ->helperText('Defaults to the article title.'),
                        Textarea::make('seo_description')
                            ->rows(2)
                            ->maxLength(500)
                            ->helperText('Defaults to the excerpt.'),
                    ]),
            ]);
    }
}
