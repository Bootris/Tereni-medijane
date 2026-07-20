<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function editor(): User
    {
        return User::factory()->create(['role' => User::ROLE_EDITOR]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_can_open_dashboard_and_all_resources(): void
    {
        Post::create([
            'title' => 'Članak', 'slug' => 'clanak', 'body' => '<p>x</p>',
            'status' => 'published', 'published_at' => now(),
        ]);
        Category::create(['name' => 'Pravo']);
        TeamMember::create(['name' => 'Dr Test', 'title' => 'Partner']);
        ContactMessage::create(['name' => 'Klijent', 'email' => 'k@example.test', 'message' => 'Upit']);

        $admin = $this->admin();

        foreach ([
            '/admin',
            '/admin/posts',
            '/admin/posts/create',
            '/admin/categories',
            '/admin/team-members',
            '/admin/contact-messages',
            '/admin/users',
            '/admin/manage-settings',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_viewing_a_contact_message_marks_it_read(): void
    {
        $message = ContactMessage::create(['name' => 'Klijent', 'email' => 'k@example.test', 'message' => 'Upit']);

        $this->actingAs($this->admin())
            ->get("/admin/contact-messages/{$message->id}")
            ->assertOk();

        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_editor_can_manage_content_but_not_users_or_settings(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->get('/admin/posts')->assertOk();
        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
        $this->actingAs($editor)->get('/admin/manage-settings')->assertForbidden();
    }

    public function test_users_without_panel_role_cannot_access_admin(): void
    {
        $user = User::factory()->create(['role' => 'none']);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }
}
