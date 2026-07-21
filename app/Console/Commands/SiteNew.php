<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class SiteNew extends Command
{
    protected $signature = 'site:new
        {--name= : Business / site name}
        {--tagline= : Short hero subtitle}
        {--email= : Public contact email}
        {--preset= : Business preset (generic|media|law|artist|shop)}
        {--primary= : Primary brand color (hex)}
        {--accent= : Accent brand color (hex)}
        {--font= : Heading font family}
        {--admin-path= : Secret admin URL slug}
        {--seed-categories : Also create the preset default blog categories}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Brand a fresh clone for a new client: writes brand settings + updates .env';

    public function handle(): int
    {
        $interactive = $this->input->isInteractive();
        $presets = array_keys(config('site.presets'));

        $name = $this->value('name', fn () => text('Business / site name', required: true), config('site.name'));
        $tagline = $this->value('tagline', fn () => text('Tagline (short hero subtitle)', default: ''), '');
        $email = $this->value('email', fn () => text('Public contact email', default: ''), '');
        $preset = $this->value('preset', fn () => select('Business preset', $presets, default: 'generic'), 'generic');
        $primary = $this->value('primary', fn () => text('Primary color (hex)', default: config('site.brand.primary')), config('site.brand.primary'));
        $accent = $this->value('accent', fn () => text('Accent color (hex)', default: config('site.brand.accent')), config('site.brand.accent'));
        $font = $this->value('font', fn () => text('Heading font', default: config('site.brand.font')), config('site.brand.font'));
        $adminPath = $this->value('admin-path', fn () => text('Admin URL slug', default: 'admin-'.Str::lower(Str::random(6))), 'admin-'.Str::lower(Str::random(6)));

        if (! in_array($preset, $presets, true)) {
            $this->error("Unknown preset '{$preset}'. One of: ".implode(', ', $presets));

            return self::FAILURE;
        }

        $this->newLine();
        $this->line("  Name:        <info>{$name}</info>");
        $this->line("  Preset:      <info>{$preset}</info>");
        $this->line("  Admin path:  <info>/{$adminPath}</info>");
        $this->line("  Colors:      <info>{$primary}</info> / <info>{$accent}</info>   Font: <info>{$font}</info>");
        $this->newLine();

        if (! $this->option('force') && $interactive && ! confirm('Apply these settings?', default: true)) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        // 1) Persist brand settings to the DB (source of truth for the API + admin).
        $settings = array_filter([
            'site_name' => $name,
            'tagline' => $tagline,
            'email' => $email,
            'theme_primary' => $primary,
            'theme_accent' => $accent,
            'theme_font' => $font,
        ], fn ($v) => $v !== null && $v !== '');

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        // 2) Update .env so config defaults + admin path match on next boot.
        $this->setEnv([
            'APP_NAME' => '"'.$name.'"',
            'SITE_NAME' => '"'.$name.'"',
            'SITE_TAGLINE' => '"'.$tagline.'"',
            'SITE_PRESET' => $preset,
            'SITE_BRAND_PRIMARY' => $primary,
            'SITE_BRAND_ACCENT' => $accent,
            'SITE_BRAND_FONT' => '"'.$font.'"',
            'ADMIN_PATH' => $adminPath,
        ]);

        // 3) Optional: seed the preset's default categories.
        if ($this->option('seed-categories')) {
            foreach (config("site.presets.$preset.categories", []) as $cat) {
                Category::firstOrCreate(['name' => $cat]);
            }
            $this->line('  Seeded preset categories.');
        }

        $this->call('config:clear');

        $this->newLine();
        $this->info('✔ Site branded.');
        $this->line("  Admin:  <comment>/{$adminPath}</comment>");
        $this->line('  Next:   set MAIL_* and APP_URL in .env, then `php artisan migrate --seed`');

        return self::SUCCESS;
    }

    /** Option value → interactive prompt (only in a TTY) → default. */
    private function value(string $option, callable $prompt, string $default): string
    {
        $given = $this->option($option);
        if ($given !== null && $given !== '') {
            return $given;
        }

        return $this->input->isInteractive() ? (string) $prompt() : $default;
    }

    /** Set or append keys in the project .env file. */
    private function setEnv(array $pairs): void
    {
        $path = base_path('.env');
        if (! is_file($path)) {
            return;
        }

        $env = file_get_contents($path);

        foreach ($pairs as $key => $value) {
            $line = $key.'='.$value;
            if (preg_match('/^'.preg_quote($key, '/').'=.*$/m', $env)) {
                $env = preg_replace('/^'.preg_quote($key, '/').'=.*$/m', $line, $env);
            } else {
                $env = rtrim($env, "\n")."\n".$line."\n";
            }
        }

        file_put_contents($path, $env);
    }
}
