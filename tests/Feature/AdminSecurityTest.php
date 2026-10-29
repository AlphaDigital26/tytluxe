<?php

namespace Tests\Feature;

use App\Filament\Resources\Hotels\Pages\CreateHotel;
use App\Models\Admin;
use App\Models\Enquiry;
use App\Models\Hotel;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * Admin panel security check (28 Oct 2026): attempts a staff member or an
 * outsider could make against /tyt-console.
 */
class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role, string $status = 'Active'): Admin
    {
        return Admin::forceCreate(['name' => $role.' person', 'email' => uniqid('a').'@example.com', 'password' => bcrypt('Right-pass-1'), 'role' => $role, 'status' => $status]);
    }

    private function as(string $role): Admin
    {
        $this->flushSession();
        $admin = $this->admin($role);
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    public function test_guessing_passwords_is_stopped_after_five_tries(): void
    {
        $admin = $this->admin('Super Admin');

        for ($i = 0; $i < 5; $i++) {
            Livewire::test(Login::class)->fillForm(['email' => $admin->email, 'password' => 'wrong-'.$i])->call('authenticate');
        }
        // Even the right password is refused until the wait is over.
        Livewire::test(Login::class)->fillForm(['email' => $admin->email, 'password' => 'Right-pass-1'])->call('authenticate');

        $this->assertGuest('admin');
    }

    public function test_staff_cannot_open_pages_beyond_their_role_by_typing_the_address(): void
    {
        $forbidden = [
            'Support' => ['/tyt-console/admins', '/tyt-console/admins/create', '/tyt-console/flight-settings', '/tyt-console/hotel-listing-settings', '/tyt-console/cruise-page-settings'],
            'Finance' => ['/tyt-console/admins', '/tyt-console/flight-settings', '/tyt-console/hotels/create', '/tyt-console/packages/create'],
            'Content' => ['/tyt-console/admins', '/tyt-console/flight-bookings', '/tyt-console/bookings', '/tyt-console/users', '/tyt-console/flight-settings', '/tyt-console/tripjack-booking-reconciliation'],
            'Analyst' => ['/tyt-console/admins', '/tyt-console/flight-settings', '/tyt-console/hotels/create', '/tyt-console/flight-bookings'],
        ];

        foreach ($forbidden as $role => $urls) {
            $this->as($role);
            foreach ($urls as $url) {
                $this->assertContains($this->get($url)->status(), [403, 404], "$role reached $url");
            }
        }

        $this->as('Support');
        $other = $this->admin('Super Admin');
        $this->assertContains($this->get('/tyt-console/admins/'.$other->id.'/edit')->status(), [403, 404]);
    }

    public function test_other_websites_cannot_frame_the_admin(): void
    {
        $this->get('/tyt-console/login')->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_a_suspended_team_member_is_locked_out_straight_away(): void
    {
        $admin = $this->as('Operations');
        $this->get('/tyt-console')->assertOk();

        $admin->update(['status' => 'Suspended']);

        $this->assertContains($this->get('/tyt-console')->status(), [302, 403]);
    }

    public function test_guest_text_is_shown_as_text_in_the_admin(): void
    {
        $this->as('Super Admin');
        Enquiry::factory()->create(['name' => '<img src=x onerror=alert(1)>Ravi', 'notes' => '<script>alert(2)</script>']);

        $this->get('/tyt-console/enquiries')->assertOk()
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;Ravi', false);
    }

    public function test_scripts_typed_into_hotel_policy_boxes_are_stripped(): void
    {
        // These boxes are typed into on the New Hotel form, and the public
        // hotel page shows them as HTML — so a script must never get through.
        $this->as('Super Admin');
        $destination = \App\Models\Destination::factory()->create(['for' => ['hotel'], 'is_active' => true]);

        Livewire::test(CreateHotel::class)
            ->fillForm([
                'destination_id' => $destination->id, 'category' => 'beach_resort', 'title' => 'Sea Breeze',
                'slug' => 'sea-breeze', 'address' => 'Beach Road, Goa', 'description' => '<p>Nice</p>',
                'star_rating' => 4, 'is_active' => true, 'is_featured' => false,
                'mandatory_fees' => '<p>City tax ₹200</p><script>alert(1)</script><img src=x onerror="alert(1)">',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $saved = (string) Hotel::where('slug', 'sea-breeze')->value('mandatory_fees');
        $this->assertStringContainsString('City tax', $saved);
        $this->assertStringNotContainsString('<script', $saved);
        $this->assertStringNotContainsString('onerror', $saved);
    }

    public function test_excel_downloads_never_contain_live_formulas(): void
    {
        $file = Excel::raw(new class implements FromArray
        {
            public function array(): array
            {
                return [['=HYPERLINK("https://evil.example","Click")', '+1+1', 'Ravi']];
            }
        }, ExcelFormat::XLSX);

        $path = tempnam(sys_get_temp_dir(), 'xl').'.xlsx';
        file_put_contents($path, $file);
        $sheet = IOFactory::load($path)->getActiveSheet();
        @unlink($path);

        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('A1')->getDataType());
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('B1')->getDataType());
        $this->assertSame('=HYPERLINK("https://evil.example","Click")', $sheet->getCell('A1')->getValue());
    }
}
