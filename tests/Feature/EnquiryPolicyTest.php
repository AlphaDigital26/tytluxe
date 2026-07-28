<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Enquiry;

class EnquiryPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_update_assigned_enquiry(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $enquiry = Enquiry::factory()->create(['assigned_agent_id' => $agent->id]);
        
        $this->assertTrue($agent->can('update', $enquiry));
    }
    
    public function test_agent_cannot_update_unassigned_enquiry(): void
    {
        $agent1 = User::factory()->create(['role' => 'agent']);
        $agent2 = User::factory()->create(['role' => 'agent']);
        $enquiry = Enquiry::factory()->create(['assigned_agent_id' => $agent2->id]);
        
        $this->assertFalse($agent1->can('update', $enquiry));
    }
    
    public function test_admin_can_update_any_enquiry(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $enquiry = Enquiry::factory()->create(['assigned_agent_id' => null]);
        
        $this->assertTrue($admin->can('update', $enquiry));
    }
}
