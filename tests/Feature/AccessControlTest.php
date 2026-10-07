<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin panel (/tyt-console) has its own Admin accounts and 'admin'
 * guard — website customers (User) never get in, and only Active admins do
 * (Admin::canAccessPanel()).
 */
class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(string $status): Admin
    {
        return Admin::create([
            'name' => 'Staff', 'email' => 'staff-'.strtolower($status).'@example.com', 'password' => 'secret-pass',
            'role' => 'Support', 'status' => $status,
        ]);
    }

    public function test_customer_cannot_access_admin_panel(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/tyt-console')->assertRedirect('/tyt-console/login');
    }

    public function test_active_admin_can_access_admin_panel(): void
    {
        $this->actingAs($this->admin('Active'), 'admin')->get('/tyt-console')->assertOk();
    }

    public function test_suspended_admin_cannot_access_admin_panel(): void
    {
        $this->actingAs($this->admin('Suspended'), 'admin')->get('/tyt-console')->assertForbidden();
    }
}
