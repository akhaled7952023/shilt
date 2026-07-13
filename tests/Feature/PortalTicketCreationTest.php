<?php

namespace Tests\Feature;

use App\Enums\TicketCategory;
use App\Enums\TicketStatus;
use App\Models\Delegate;
use App\Models\FinancialRequest;
use App\Models\SupportTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * P8-005 — Feature tests: Ticket creation (delegate portal)
 */
class PortalTicketCreationTest extends TestCase
{
    use RefreshDatabase;

    private Delegate $delegate;
    private Delegate $otherDelegate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->delegate      = $this->makeDelegate('11111111');
        $this->otherDelegate = $this->makeDelegate('22222222');
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    /** @test */
    public function authenticated_delegate_can_view_ticket_list(): void
    {
        $this->actingAs($this->delegate, 'delegate')
            ->get(route('portal.support.tickets.index'))
            ->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_is_redirected_from_ticket_list(): void
    {
        $this->get(route('portal.support.tickets.index'))
            ->assertRedirect();
    }

    // ── Category picker ───────────────────────────────────────────────────────

    /** @test */
    public function step1_category_picker_loads(): void
    {
        $this->actingAs($this->delegate, 'delegate')
            ->get(route('portal.support.tickets.create'))
            ->assertStatus(200)
            ->assertSee(TicketCategory::AdvanceRequest->value);
    }

    /** @test */
    public function step2_loads_for_valid_category(): void
    {
        $this->actingAs($this->delegate, 'delegate')
            ->get(route('portal.support.tickets.create', ['category' => 'general_inquiry']))
            ->assertStatus(200);
    }

    /** @test */
    public function step2_falls_back_to_step1_for_invalid_category(): void
    {
        $this->actingAs($this->delegate, 'delegate')
            ->get(route('portal.support.tickets.create', ['category' => 'invalid_category']))
            ->assertStatus(200);
    }

    // ── Store — non-financial ticket ─────────────────────────────────────────

    /** @test */
    public function delegate_can_create_general_inquiry_ticket(): void
    {
        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.store'), [
                'category'    => 'general_inquiry',
                'subject'     => 'Test Subject',
                'description' => 'This is a test description with enough characters.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('support_tickets', [
            'delegate_id' => $this->delegate->id,
            'category'    => 'general_inquiry',
            'platform'    => 'hungerstation',
            'source'      => 'portal',
            'priority'    => 'normal',
            'status'      => 'open',
        ]);
    }

    /** @test */
    public function non_financial_ticket_does_not_create_financial_request(): void
    {
        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.store'), [
                'category'    => 'general_inquiry',
                'subject'     => 'Test',
                'description' => 'A description that is long enough to pass validation.',
            ]);

        $ticket = SupportTicket::where('delegate_id', $this->delegate->id)->firstOrFail();
        $this->assertNull($ticket->financialRequest);
    }

    /** @test */
    public function ticket_number_is_generated_in_correct_format(): void
    {
        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.store'), [
                'category'    => 'general_inquiry',
                'subject'     => 'Subject',
                'description' => 'A description that is long enough to pass validation.',
            ]);

        $ticket = SupportTicket::where('delegate_id', $this->delegate->id)->firstOrFail();
        $this->assertMatchesRegularExpression('/^TK-\d{4}-\d{5,}$/', $ticket->ticket_number);
    }

    /** @test */
    public function description_becomes_first_reply_in_thread(): void
    {
        $description = 'This is the initial description that should appear in the reply thread.';

        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.store'), [
                'category'    => 'general_inquiry',
                'subject'     => 'Subject',
                'description' => $description,
            ]);

        $ticket = SupportTicket::where('delegate_id', $this->delegate->id)->firstOrFail();
        $this->assertDatabaseHas('support_ticket_replies', [
            'ticket_id'           => $ticket->id,
            'author_type'         => 'delegate',
            'author_delegate_id'  => $this->delegate->id,
            'content'             => $description,
            'is_internal_note'    => 0,
        ]);
    }

    /** @test */
    public function sla_deadlines_are_populated_after_creation(): void
    {
        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.store'), [
                'category'    => 'general_inquiry',
                'subject'     => 'Subject',
                'description' => 'A description that is long enough to pass validation.',
            ]);

        $ticket = SupportTicket::where('delegate_id', $this->delegate->id)->firstOrFail();
        $this->assertNotNull($ticket->sla_first_response_deadline);
        $this->assertNotNull($ticket->sla_resolution_deadline);
    }

    // ── Store — financial ticket ─────────────────────────────────────────────

    /** @test */
    public function financial_ticket_creates_financial_request_row(): void
    {
        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.store'), [
                'category'         => 'advance_request',
                'subject'          => 'Advance Request Subject',
                'description'      => 'I need an advance payment for personal reasons.',
                'requested_amount' => '500.00',
            ]);

        $ticket = SupportTicket::where('delegate_id', $this->delegate->id)->firstOrFail();
        $this->assertNotNull($ticket->financialRequest);
        $this->assertEquals('500.00', number_format($ticket->financialRequest->requested_amount, 2));
        $this->assertEquals('pending', $ticket->financialRequest->status->value);
    }

    /** @test */
    public function financial_ticket_requires_requested_amount(): void
    {
        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.store'), [
                'category'    => 'advance_request',
                'subject'     => 'Subject',
                'description' => 'A description that is long enough to pass validation.',
                // 'requested_amount' missing
            ])
            ->assertSessionHasErrors(['requested_amount']);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    /** @test */
    public function invalid_category_is_rejected(): void
    {
        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.store'), [
                'category'    => 'not_a_real_category',
                'subject'     => 'Subject',
                'description' => 'A description that is long enough.',
            ])
            ->assertRedirect(); // returns back() with error
    }

    /** @test */
    public function description_shorter_than_20_chars_fails_validation(): void
    {
        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.store'), [
                'category'    => 'general_inquiry',
                'subject'     => 'Subject',
                'description' => 'Too short',  // < 20 chars
            ])
            ->assertSessionHasErrors(['description']);
    }

    /** @test */
    public function subject_is_required(): void
    {
        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.store'), [
                'category'    => 'general_inquiry',
                'subject'     => '',
                'description' => 'A description that is long enough to pass validation.',
            ])
            ->assertSessionHasErrors(['subject']);
    }

    // ── Ownership isolation ───────────────────────────────────────────────────

    /** @test */
    public function delegate_cannot_view_another_delegates_ticket(): void
    {
        // Create a ticket belonging to otherDelegate
        $ticket = $this->insertTicket($this->otherDelegate->id);

        // Try to access it as delegate (different delegate)
        $this->actingAs($this->delegate, 'delegate')
            ->get(route('portal.support.tickets.show', $ticket))
            ->assertStatus(403);
    }

    /** @test */
    public function delegate_can_only_see_own_tickets_in_list(): void
    {
        $this->insertTicket($this->delegate->id, ['subject' => 'My Ticket']);
        $this->insertTicket($this->otherDelegate->id, ['subject' => 'Other Ticket']);

        $response = $this->actingAs($this->delegate, 'delegate')
            ->get(route('portal.support.tickets.index'));

        $response->assertStatus(200);
        // Only 1 ticket belongs to $this->delegate
        $tickets = SupportTicket::forDelegate($this->delegate->id)->get();
        $this->assertCount(1, $tickets);
        $this->assertEquals('My Ticket', $tickets->first()->subject);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeDelegate(string $nationalId): Delegate
    {
        $id = DB::table('delegates')->insertGetId([
            'name'               => 'Test Delegate ' . $nationalId,
            'national_id'        => $nationalId,
            'portal_enabled'     => true,
            'portal_first_login' => false,
            'status'             => 'active',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return Delegate::findOrFail($id);
    }

    private function insertTicket(int $delegateId, array $overrides = []): SupportTicket
    {
        $id = DB::table('support_tickets')->insertGetId(array_merge([
            'ticket_number'    => 'TK-' . now()->year . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'platform'         => 'hungerstation',
            'source'           => 'portal',
            'delegate_id'      => $delegateId,
            'category'         => 'general_inquiry',
            'priority'         => 'normal',
            'subject'          => 'Test Ticket',
            'status'           => 'open',
            'opened_at'        => now(),
            'last_activity_at' => now(),
            'created_by'       => $delegateId,
            'created_at'       => now(),
            'updated_at'       => now(),
        ], $overrides));

        return SupportTicket::findOrFail($id);
    }
}
