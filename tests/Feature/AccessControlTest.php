<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_admin_panel(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        
        $response = $this->actingAs($customer)->get('/admin');
        
        $response->assertStatus(403);
    }
    
    public function test_agent_can_access_admin_panel(): void
    {
        $agent = User::factory()->create(['role' => 'agent', 'is_active' => true]);
        
        $response = $this->actingAs($agent)->get('/admin');
        
        $response->assertStatus(200);
    }
    
    public function test_inactive_agent_cannot_access_admin_panel(): void
    {
        $agent = User::factory()->create(['role' => 'agent', 'is_active' => false]);
        
        $response = $this->actingAs($agent)->get('/admin');
        
        $response->assertStatus(403);
    }
}
