<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_export_reports(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/reports')->assertOk();

        $csv = $this->actingAs($admin)->get('/admin/reports/export');
        $csv->assertOk();
        $this->assertStringContainsString('Ticket ID', $csv->streamedContent());
    }

    public function test_employee_cannot_open_reports(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee)->get('/admin/reports')->assertForbidden();
    }
}