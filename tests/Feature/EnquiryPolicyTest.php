<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** EnquiryPolicy: staff edit enquiries assigned to them; Super Admin edits any. */
class EnquiryPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(string $role): Admin
    {
        return Admin::create([
            'name' => $role, 'email' => uniqid('staff').'@example.com', 'password' => 'secret-pass',
            'role' => $role, 'status' => 'Active',
        ]);
    }

    public function test_staff_can_update_assigned_enquiry(): void
    {
        $agent = $this->admin('Support');
        $enquiry = Enquiry::factory()->create(['assigned_agent_id' => $agent->id]);

        $this->assertTrue($agent->can('update', $enquiry));
    }

    public function test_staff_cannot_update_enquiry_assigned_to_someone_else(): void
    {
        $agent1 = $this->admin('Support');
        $agent2 = $this->admin('Support');
        $enquiry = Enquiry::factory()->create(['assigned_agent_id' => $agent2->id]);

        $this->assertFalse($agent1->can('update', $enquiry));
    }

    public function test_super_admin_can_update_any_enquiry(): void
    {
        $admin = $this->admin('Super Admin');
        $enquiry = Enquiry::factory()->create(['assigned_agent_id' => null]);

        $this->assertTrue($admin->can('update', $enquiry));
    }
}
