<?php

namespace App\Http\Controllers;

use App\Models\Post;

class SitemapController extends Controller
{
    public function index()
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $posts = Post::published()->orderByDesc('published_at')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach (['sr', 'en'] as $locale) {
            $xml .= $this->url("{$baseUrl}/{$locale}", now()->toISOString(), 'weekly', '1.0');
        }

        $xml .= $this->url("{$baseUrl}/blog", now()->toISOString(), 'daily', '0.8');

        foreach ($posts as $post) {
            $xml .= $this->url(
                "{$baseUrl}/blog/{$post->slug}",
                $post->updated_at->toISOString(),
                'monthly',
                '0.6',
            );
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    private function url(string $loc, string $lastmod, string $changefreq, string $priority): string
    {
        return "  <url>\n"
            . '    <loc>' . htmlspecialchars($loc) . "</loc>\n"
            . "    <lastmod>{$lastmod}</lastmod>\n"
            . "    <changefreq>{$changefreq}</changefreq>\n"
            . "    <priority>{$priority}</priority>\n"
            . "  </url>\n";
    }
}
