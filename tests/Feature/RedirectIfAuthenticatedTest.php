<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RedirectIfAuthenticatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_can_access_login_page(): void
    {
        $response = $this->getJson('/api/test-login-access');
        
        $response->assertStatus(200);
    }

    public function test_authenticated_admin_is_redirected_from_login_page(): void
    {
        $admin = User::factory()->create(['is_crew' => false]);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/test-login-access');
        
        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Already authenticated',
                    'redirect_to' => '/admin',
                    'user_type' => 'admin'
                ]);
    }

    public function test_authenticated_crew_is_redirected_from_login_page(): void
    {
        $crew = User::factory()->create(['is_crew' => true]);
        Sanctum::actingAs($crew);

        $response = $this->getJson('/api/test-login-access');
        
        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Already authenticated',
                    'redirect_to' => '/home',
                    'user_type' => 'crew'
                ]);
    }
}