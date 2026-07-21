<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page
{
    protected string $view = 'filament.pages.manage-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 21;

    protected static ?string $title = 'Site settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(Setting::allCached());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identity')
                    ->columns(2)
                    ->components([
                        TextInput::make('site_name')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Shown in the header, footer and browser title.'),
                        TextInput::make('tagline')
                            ->helperText('Short line under the name (hero subtitle).'),
                        FileUpload::make('logo')
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->helperText('Optional. Used in header and as favicon source.'),
                    ]),
                Section::make('Branding')
                    ->columns(3)
                    ->description('Colors and font are served to the frontend and applied as CSS variables — no code change to rebrand.')
                    ->components([
                        ColorPicker::make('theme_primary')->label('Primary color'),
                        ColorPicker::make('theme_accent')->label('Accent color'),
                        TextInput::make('theme_font')
                            ->label('Heading font')
                            ->helperText('A Google Font family name, e.g. Inter, Playfair Display.'),
                    ]),
                Section::make('Contact details')
                    ->columns(2)
                    ->components([
                        TextInput::make('email')
                            ->label('Public email')
                            ->email(),
                        TextInput::make('phone')->tel(),
                        Textarea::make('address')->rows(2),
                        TextInput::make('working_hours')
                            ->helperText('E.g. Mon–Fri 09:00–17:00'),
                        TextInput::make('contact_notify_email')
                            ->label('Contact form notifications go to')
                            ->email()
                            ->helperText('Defaults to the public email if empty.'),
                    ]),
                Section::make('Social profiles')
                    ->columns(3)
                    ->components([
                        TextInput::make('facebook')->url(),
                        TextInput::make('instagram')->url(),
                        TextInput::make('linkedin_url')->label('LinkedIn')->url(),
                    ]),
                Section::make('Integrations')
                    ->components([
                        TextInput::make('map_embed')
                            ->label('Google Maps embed URL')
                            ->url()
                            ->helperText('Google Maps → Share → Embed a map → copy the src URL of the iframe.'),
                        TextInput::make('calendly_url')
                            ->label('Booking URL (Calendly / Cal.com)')
                            ->url()
                            ->helperText('Optional — adds a "Schedule consultation" button on the site.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
