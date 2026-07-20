<?php

namespace Tests\Feature;

use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    private function publishedPost(array $attributes = []): Post
    {
        return Post::create(array_merge([
            'title' => 'Test članak',
            'slug' => 'test-clanak',
            'excerpt' => 'Kratak opis.',
            'body' => '<p>Sadržaj članka.</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ], $attributes));
    }

    public function test_root_redirects_to_default_locale(): void
    {
        $this->get('/')->assertRedirect('/sr');
    }

    public function test_home_renders_in_both_locales(): void
    {
        $this->get('/sr')->assertOk()->assertSee('Pravna sigurnost.');
        $this->get('/en')->assertOk()->assertSee('Legal certainty.');
    }

    public function test_blog_index_shows_published_posts_only(): void
    {
        $this->publishedPost();
        Post::create([
            'title' => 'Draft članak',
            'slug' => 'draft-clanak',
            'body' => '<p>Draft.</p>',
            'status' => 'draft',
        ]);
        Post::create([
            'title' => 'Zakazan članak',
            'slug' => 'zakazan-clanak',
            'body' => '<p>Scheduled.</p>',
            'status' => 'published',
            'published_at' => now()->addWeek(),
        ]);

        $this->get('/blog')
            ->assertOk()
            ->assertSee('Test članak')
            ->assertDontSee('Draft članak')
            ->assertDontSee('Zakazan članak');
    }

    public function test_blog_post_page_renders(): void
    {
        $post = $this->publishedPost(['video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);

        $this->get("/blog/{$post->slug}")
            ->assertOk()
            ->assertSee('Test članak')
            ->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
    }

    public function test_draft_post_returns_404(): void
    {
        $this->publishedPost(['slug' => 'skriveni', 'status' => 'draft']);

        $this->get('/blog/skriveni')->assertNotFound();
    }

    public function test_sitemap_lists_published_posts(): void
    {
        $post = $this->publishedPost();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee("/blog/{$post->slug}");
    }

    public function test_contact_form_stores_message_and_sends_mail(): void
    {
        Mail::fake();
        Setting::set('email', 'office@example.test');

        $this->from('/sr')->post('/contact', [
            'name' => 'Petar Petrović',
            'email' => 'petar@example.test',
            'message' => 'Trebam pravni savet.',
        ])->assertRedirect('/sr')->assertSessionHas('contact_success');

        $this->assertDatabaseHas('contact_messages', ['email' => 'petar@example.test']);
        Mail::assertSent(ContactFormSubmitted::class);
    }

    public function test_contact_honeypot_blocks_bots_silently(): void
    {
        $this->post('/contact', [
            'name' => 'Bot',
            'email' => 'bot@example.test',
            'message' => 'spam',
            'website' => 'http://spam.example',
        ])->assertRedirect();

        $this->assertSame(0, ContactMessage::count());
    }
}
