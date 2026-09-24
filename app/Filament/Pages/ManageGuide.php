<?php

namespace App\Filament\Pages;

use App\Support\Tereni\GuideContent;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Editable text of the public /uputstva page. Content, not config, so
 * editors may change it too (unlike Site settings).
 */
class ManageGuide extends Page
{
    protected string $view = 'filament.pages.manage-guide';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Tereni Medijana';

    protected static ?int $navigationSort = 90;

    protected static ?string $navigationLabel = 'Uputstva';

    protected static ?string $title = 'Uputstva';

    protected static ?string $slug = 'uputstva';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) config('site.features.tereni') && auth()->check();
    }

    public function mount(): void
    {
        $this->form->fill(GuideContent::get());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Uvod')
                    ->components([
                        Textarea::make('lead')
                            ->label('Uvodni tekst')
                            ->rows(3)
                            ->helperText('Prikazuje se ispod naslova „Uputstva”.'),
                    ]),
                Section::make('Sekcije')
                    ->description('Svaka sekcija dobija svoju stavku u sadržaju sa strane. Redosled menjaš prevlačenjem. '
                        .'U tekst možeš upisati [kategorije] ili [statusi] (sam u redu) i na sajtu će se prikazati aktuelna lista kategorija prijave, odnosno statusa.')
                    ->components([
                        Repeater::make('sections')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('title')
                                    ->label('Naslov')
                                    ->required()
                                    ->maxLength(120),
                                RichEditor::make('body')
                                    ->label('Tekst')
                                    ->toolbarButtons([
                                        ['bold', 'italic', 'link'],
                                        ['h3', 'blockquote'],
                                        ['bulletList', 'orderedList'],
                                        ['undo', 'redo'],
                                    ]),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->addActionLabel('Dodaj sekciju')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->collapsed()
                            ->minItems(1),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        GuideContent::save($this->form->getState());

        Notification::make()
            ->title('Uputstva sačuvana')
            ->success()
            ->send();
    }

    public function restoreDefaults(): void
    {
        $this->form->fill(GuideContent::defaults());

        Notification::make()
            ->title('Vraćen podrazumevani tekst. Klikni „Sačuvaj” da ga objaviš.')
            ->info()
            ->send();
    }
}
