<?php

namespace Tests\Feature;

use App\Notifications\TicketActivity;
use Illuminate\Support\Facades\Notification;
use App\Models\TicketStatus;
use Database\Seeders\LookupSeeder;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DatabaseSeeder::class);
    }

    private function user(string $role): User
    {
        return User::where('role', $role)->first();
    }

    private function ticketFor(User $owner): Ticket
    {
        return Ticket::create([
            'title' => 'Printer down', 'description' => 'Jammed', 'category_id' => 1,
            'priority_id' => 2, 'status_id' => Ticket::OPEN, 'created_by' => $owner->id,
        ]);
    }

    public function test_employee_can_create_ticket_with_sla_deadline(): void
    {
        $emp = $this->user('employee');

        $this->actingAs($emp)->post('/tickets', [
            'title' => 'VPN issue', 'description' => 'Cannot connect', 'category_id' => 3, 'priority_id' => 3,
        ])->assertRedirect();

        $ticket = Ticket::where('created_by', $emp->id)->first();
        $this->assertNotNull($ticket);
        $this->assertNotNull($ticket->due_at);
    }

    public function test_employee_cannot_view_another_employees_ticket(): void
    {
        $ticket = $this->ticketFor($this->user('employee'));
        $other = User::factory()->create(['role' => 'employee']);

        $this->actingAs($other)->get("/tickets/{$ticket->id}")->assertForbidden();
    }

    public function test_employee_cannot_change_status(): void
    {
        $emp = $this->user('employee');
        $ticket = $this->ticketFor($emp);

        $this->actingAs($emp)->put("/tickets/{$ticket->id}", ['status_id' => 5])->assertForbidden();
    }

    public function test_assigning_a_technician_moves_ticket_to_assigned_and_notifies_creator(): void
    {
        $emp = $this->user('employee');
        $tech = $this->user('support');
        $ticket = $this->ticketFor($emp);

        $this->actingAs($tech)->put("/tickets/{$ticket->id}", [
            'status_id' => Ticket::OPEN, 'assigned_to' => $tech->id,
        ]);

        $this->assertSame(Ticket::ASSIGNED, $ticket->fresh()->status_id);
        $this->assertCount(1, $emp->notifications);
    }

    public function test_resolving_a_ticket_sets_resolved_at(): void
    {
        $ticket = $this->ticketFor($this->user('employee'));

        $this->actingAs($this->user('support'))->put("/tickets/{$ticket->id}", ['status_id' => Ticket::RESOLVED]);

        $this->assertNotNull($ticket->fresh()->resolved_at);
    }

    public function test_only_admin_can_open_admin_pages(): void
    {
        $this->actingAs($this->user('employee'))->get('/admin/users')->assertForbidden();
        $this->actingAs($this->user('support'))->get('/admin/categories')->assertForbidden();
        $this->actingAs($this->user('admin'))->get('/admin/users')->assertOk();
    }

    public function test_api_requires_authentication(): void
    {
        $this->getJson('/api/tickets')->assertUnauthorized();
    }

    public function test_api_login_returns_token_and_rejects_wrong_password(): void
    {
        $this->postJson('/api/login', ['email' => 'emp@demo.test', 'password' => 'password'])
            ->assertOk()->assertJsonStructure(['token', 'user']);

        $this->postJson('/api/login', ['email' => 'emp@demo.test', 'password' => 'wrong'])
            ->assertStatus(422);
    }

    public function test_api_employee_only_sees_own_tickets(): void
    {
        $emp = $this->user('employee');
        $other = User::factory()->create(['role' => 'employee']);
        $this->ticketFor($emp);
        $this->ticketFor($other);

        Sanctum::actingAs($emp);
        $this->getJson('/api/tickets')->assertOk()->assertJsonPath('total', 1);
    }

    public function test_api_hides_internal_notes_from_employee_but_not_from_staff(): void
    {
        $emp = $this->user('employee');
        $tech = $this->user('support');
        $ticket = $this->ticketFor($emp);
        $ticket->comments()->create(['user_id' => $tech->id, 'body' => 'secret note', 'is_internal' => true]);

        Sanctum::actingAs($emp);
        $this->getJson("/api/tickets/{$ticket->id}")->assertOk()->assertJsonMissing(['body' => 'secret note']);

        Sanctum::actingAs($tech);
        $this->getJson("/api/tickets/{$ticket->id}")->assertOk()->assertJsonFragment(['body' => 'secret note']);
    }

    public function test_api_employee_cannot_update_status(): void
    {
        $emp = $this->user('employee');
        $ticket = $this->ticketFor($emp);

        Sanctum::actingAs($emp);
        $this->putJson("/api/tickets/{$ticket->id}", ['status_id' => 5])->assertForbidden();
    }

         public function test_status_and_assignment_changes_are_logged_with_the_actor(): void
    {
        $tech = $this->user('support');
        $ticket = $this->ticketFor($this->user('employee'));

        $this->actingAs($tech)->put("/tickets/{$ticket->id}", [
            'status_id' => Ticket::IN_PROGRESS, 'assigned_to' => $tech->id,
        ]);

        $this->assertDatabaseHas('ticket_activities', [
            'ticket_id' => $ticket->id, 'user_id' => $tech->id, 'description' => 'Status changed to In Progress',
        ]);
        $this->assertDatabaseHas('ticket_activities', [
            'ticket_id' => $ticket->id, 'user_id' => $tech->id, 'description' => "Assigned to {$tech->name}",
        ]);
    }

    public function test_api_changes_are_logged_with_the_actor(): void
    {
        $tech = $this->user('support');
        $ticket = $this->ticketFor($this->user('employee'));

        Sanctum::actingAs($tech);
        $this->putJson("/api/tickets/{$ticket->id}", ['status_id' => Ticket::RESOLVED])->assertOk();

        $this->assertDatabaseHas('ticket_activities', [
            'ticket_id' => $ticket->id, 'user_id' => $tech->id, 'description' => 'Status changed to Resolved',
        ]);
    }

        public function test_lookup_seeder_can_run_twice_without_duplicates(): void
    {
        $this->seed(LookupSeeder::class);

        $this->assertSame(6, TicketStatus::count());
        $this->assertSame('Closed', TicketStatus::find(Ticket::CLOSED)->name);
    }

    public function test_create_admin_command_makes_an_admin_and_rejects_short_passwords(): void
    {
        $this->artisan('servicedesk:create-admin', ['email' => 'boss@example.com', 'name' => 'Boss'])
            ->expectsQuestion('Password (min 12 characters)', 'short')
            ->assertFailed();
        $this->assertDatabaseMissing('users', ['email' => 'boss@example.com']);

        $this->artisan('servicedesk:create-admin', ['email' => 'boss@example.com', 'name' => 'Boss'])
            ->expectsQuestion('Password (min 12 characters)', 'a-long-password-123')
            ->assertSuccessful();
        $this->assertDatabaseHas('users', ['email' => 'boss@example.com', 'role' => 'admin']);
    }

        public function test_ticket_list_renders_status_badge_and_overdue_flag(): void
    {
        $emp = $this->user('employee');
        $ticket = $this->ticketFor($emp);
        $ticket->update(['due_at' => now()->subHour()]);

        $this->actingAs($emp)->get('/tickets')->assertOk()->assertSee('Open')->assertSee('Overdue');
    }

        public function test_web_and_api_route_names_do_not_collide(): void
    {
        $this->assertSame(url('/tickets'), route('tickets.index'));
        $this->assertSame(url('/api/tickets'), route('api.tickets.index'));
    }

        public function test_new_ticket_notifies_staff_by_bell_and_email(): void
    {
        Notification::fake();

        $this->actingAs($this->user('employee'))->post('/tickets', [
            'title' => 'VPN issue', 'description' => 'Cannot connect', 'category_id' => 3, 'priority_id' => 3,
        ]);

        Notification::assertSentTo(
            $this->user('support'),
            TicketActivity::class,
            fn ($n, $channels) => in_array('database', $channels) && in_array('mail', $channels)
        );
    }

}