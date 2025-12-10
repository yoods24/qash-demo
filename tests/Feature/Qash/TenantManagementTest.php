<?php

declare(strict_types=1);

namespace Tests\Feature\Qash;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_tenant_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/qash');

        $response->assertOk();
        $response->assertViewIs('qash.dashboard');
    }

    public function test_admin_can_create_tenant_with_primary_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post('/qash/tenants', [
            'name' => 'Acme Cafe',
            'slug' => 'acme-cafe',
            'description' => 'A demo tenant for testing.',
            'admin_first_name' => 'Jane',
            'admin_last_name' => 'Doe',
            'admin_email' => 'jane@acme.test',
            'admin_phone' => '+62 812-0000-0000',
            'admin_password' => 'password123',
            'admin_password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('qash.dashboard'));

        $this->assertDatabaseHas('tenants', [
            'id' => 'acme-cafe',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@acme.test',
            'tenant_id' => 'acme-cafe',
            'is_admin' => true,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/qash')->assertForbidden();
        $this->actingAs($user)->post('/qash/tenants', [])->assertForbidden();
    }
}
