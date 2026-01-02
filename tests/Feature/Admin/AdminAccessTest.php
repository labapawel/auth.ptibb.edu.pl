<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_is_redirected_from_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'username' => 'basic',
            'permission' => [User::PERMISSION_TASK],
        ]);

        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('welcome'));
        $response->assertSessionHas('error');
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin',
            'permission' => [User::PERMISSION_ADMIN],
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewIs('admin.dashboard');
    }
}
