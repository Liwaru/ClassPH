<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminChatbotFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_dashboard_shows_chatbot(): void
    {
        $user = $this->createSuperadmin();

        $response = $this->withSession([
            'logged_in' => true,
            'user' => $this->sessionUserPayload($user),
        ])->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('id="chatbotShell"', false);
        $response->assertSee('id="chatbotToggle"', false);
    }

    public function test_superadmin_can_open_chatbot_workspace_page(): void
    {
        $user = $this->createSuperadmin();

        $response = $this->withSession([
            'logged_in' => true,
            'user' => $this->sessionUserPayload($user),
        ])->get(route('superadmin.chatbot'));

        $response->assertOk();
        $response->assertSee('Chatbot Superadmin');
        $response->assertSee('id="chatbotShell"', false);
        $response->assertSee('Chatbot', false);
    }

    private function createSuperadmin(): User
    {
        return User::create([
            'nis' => '3001',
            'nama' => 'Superadmin Test',
            'email' => 'superadmin@example.test',
            'otp_enabled' => false,
            'password' => 'secret123',
            'level' => 3,
            'kelas' => null,
        ]);
    }

    private function sessionUserPayload(User $user): array
    {
        return [
            'id_user' => $user->id_user,
            'nis' => $user->nis,
            'nama' => $user->nama,
            'email' => $user->email,
            'otp_enabled' => false,
            'level' => 3,
            'role_label' => 'Superadmin',
        ];
    }
}
