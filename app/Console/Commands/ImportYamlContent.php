<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\TeamMember;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class ImportYamlContent extends Command
{
    protected $signature = 'site:import-yaml {--path= : Directory with YAML files (defaults to storage/content/posts)}';

    protected $description = 'One-off import of the legacy YAML flat-file content into the database (idempotent, matched by slug)';

    public function handle(): int
    {
        $path = $this->option('path') ?: storage_path('content/posts');

        if (! File::isDirectory($path)) {
            $this->error("Directory not found: {$path}");

            return self::FAILURE;
        }

        $posts = 0;
        $profiles = 0;
        $skipped = 0;

        $files = File::glob($path.'/*.yaml');

        // Profiles carry a "priority" (higher = more prominent); collect first so
        // sort_order can be assigned from the final ordering.
        $profileData = [];

        foreach ($files as $file) {
            $data = Yaml::parseFile($file);

            if (! is_array($data) || empty($data['title']) || empty($data['slug'])) {
                $skipped++;

                continue;
            }

            $type = $data['content_type'] ?? 'article';

            if ($type === 'profile') {
                $profileData[] = $data;

                continue;
            }

            Post::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'] ?? null,
                    'body' => Str::markdown($data['body'] ?? ''),
                    'author_name' => $data['author'] ?? null,
                    'status' => ($data['status'] ?? 'draft') === 'published' ? 'published' : 'draft',
                    'published_at' => isset($data['published_at']) ? Carbon::parse($data['published_at']) : null,
                    'featured_image' => null,
                    'show_on_home' => (bool) ($data['show_on_home'] ?? false),
                ],
            );
            $posts++;
        }

        usort($profileData, fn ($a, $b) => ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0));

        foreach ($profileData as $order => $data) {
            TeamMember::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['title'],
                    'title' => $data['role'] ?? null,
                    'bio' => Str::markdown($data['body'] ?? ''),
                    'photo' => null,
                    'sort_order' => $order,
                    'visible' => ($data['status'] ?? '') === 'published',
                ],
            );
            $profiles++;
        }

        $this->info("Imported {$posts} articles and {$profiles} team members. Skipped {$skipped} files.");

        return self::SUCCESS;
    }
}
