<?php

namespace Tests\Feature;

use App\Enums\FinancialRequestStatus;
use App\Enums\PendingEntryStatus;
use App\Enums\TicketStatus;
use App\Models\Delegate;
use App\Models\FinancialRequest;
use App\Models\MonthlyPeriod;
use App\Models\PendingFinancialEntry;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * P8-007 — Feature tests: Financial request approval workflow
 */
class FinancialRequestApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User         $admin;
    private User         $superAdmin;
    private Delegate     $delegate;
    private MonthlyPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin      = $this->makeAdmin(isSuperAdmin: false);
        $this->superAdmin = $this->makeAdmin(isSuperAdmin: true);
        $this->delegate   = $this->makeDelegate();
        $this->period     = $this->makePeriod();
    }

    // ── Approve ───────────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_approve_a_pending_financial_request(): void
    {
        [$ticket, $fr] = $this->makeFinancialTicket();

        $this->actingAs($this->admin)
            ->patch(route('dashboard.support.financial-requests.approve', $fr), [
                'approved_amount'   => '450.00',
                'deduction_type'    => 'advance',
                'is_benefit'        => '0',
                'settlement_month'  => now()->month,
                'notes'             => 'Approved for testing.',
            ])
            ->assertRedirect();

        $fr->refresh();
        $this->assertEquals(FinancialRequestStatus::Approved, $fr->status);
        $this->assertEquals(450.00, (float) $fr->approved_amount);
        $this->assertEquals('advance', $fr->approved_deduction_type);
        $this->assertEquals($this->admin->id, $fr->reviewed_by);
    }

    /** @test */
    public function approval_creates_a_pending_financial_entry(): void
    {
        [$ticket, $fr] = $this->makeFinancialTicket();

        $this->actingAs($this->admin)
            ->patch(route('dashboard.support.financial-requests.approve', $fr), [
                'approved_amount'   => '300.00',
                'deduction_type'    => 'advance',
                'is_benefit'        => '0',
                'settlement_month'  => now()->month,
            ]);

        $this->assertDatabaseHas('pending_financial_entries', [
            'financial_request_id' => $fr->id,
            'delegate_id'          => $this->delegate->id,
            'settlement_month'     => now()->month,
            'settlement_year'      => now()->year,
            'deduction_type'       => 'advance',
            'amount'               => '300.00',
            'status'               => PendingEntryStatus::Pending->value,
            'created_by'           => $this->admin->id,
        ]);
    }

    /** @test */
    public function approval_resolves_the_ticket(): void
    {
        [$ticket, $fr] = $this->makeFinancialTicket();

        $this->actingAs($this->admin)
            ->patch(route('dashboard.support.financial-requests.approve', $fr), [
                'approved_amount'   => '200.00',
                'deduction_type'    => 'advance',
                'is_benefit'        => '0',
                'settlement_month'  => now()->month,
            ]);

        $ticket->refresh();
        $this->assertEquals(TicketStatus::Resolved, $ticket->status);
        $this->assertNotNull($ticket->resolved_at);
        $this->assertNotNull($ticket->close_grace_deadline);
    }

    /** @test */
    public function double_approval_is_rejected_with_no_duplicate_entry(): void
    {
        [$ticket, $fr] = $this->makeFinancialTicket();

        $payload = [
            'approved_amount'   => '200.00',
            'deduction_type'    => 'advance',
            'is_benefit'        => '0',
            'settlement_month'  => now()->month,
        ];

        // First approval succeeds
        $this->actingAs($this->admin)->patch(
            route('dashboard.support.financial-requests.approve', $fr), $payload
        );

        // Second approval is a no-op (already reviewed)
        $this->actingAs($this->admin)->patch(
            route('dashboard.support.financial-requests.approve', $fr), $payload
        );

        // Still only one entry in the database
        $this->assertDatabaseCount('pending_financial_entries', 1);
    }

    /** @test */
    public function approval_requires_valid_deduction_type(): void
    {
        [$ticket, $fr] = $this->makeFinancialTicket();

        $this->actingAs($this->admin)
            ->patch(route('dashboard.support.financial-requests.approve', $fr), [
                'approved_amount'   => '200.00',
                'deduction_type'    => 'invalid_type',
                'is_benefit'        => '0',
                'settlement_month'  => now()->month,
            ])
            ->assertSessionHasErrors(['deduction_type']);
    }

    /** @test */
    public function approval_requires_amount_greater_than_zero(): void
    {
        [$ticket, $fr] = $this->makeFinancialTicket();

        $this->actingAs($this->admin)
            ->patch(route('dashboard.support.financial-requests.approve', $fr), [
                'approved_amount'   => '0',
                'deduction_type'    => 'advance',
                'is_benefit'        => '0',
                'settlement_month'  => now()->month,
            ])
            ->assertSessionHasErrors(['approved_amount']);
    }

    // ── Reject ────────────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_reject_a_pending_financial_request(): void
    {
        [$ticket, $fr] = $this->makeFinancialTicket();

        $this->actingAs($this->admin)
            ->patch(route('dashboard.support.financial-requests.reject', $fr), [
                'rejection_reason' => 'هذا الطلب لا يستوفي المتطلبات المطلوبة للموافقة.',
            ])
            ->assertRedirect();

        $fr->refresh();
        $this->assertEquals(FinancialRequestStatus::Rejected, $fr->status);
        $this->assertNotNull($fr->rejection_reason);
    }

    /** @test */
    public function rejection_resolves_the_ticket(): void
    {
        [$ticket, $fr] = $this->makeFinancialTicket();

        $this->actingAs($this->admin)
            ->patch(route('dashboard.support.financial-requests.reject', $fr), [
                'rejection_reason' => 'هذا الطلب لا يستوفي المتطلبات المطلوبة للموافقة.',
            ]);

        $ticket->refresh();
        $this->assertEquals(TicketStatus::Resolved, $ticket->status);
    }

    /** @test */
    public function rejection_requires_reason_of_minimum_10_chars(): void
    {
        [$ticket, $fr] = $this->makeFinancialTicket();

        $this->actingAs($this->admin)
            ->patch(route('dashboard.support.financial-requests.reject', $fr), [
                'rejection_reason' => 'short',
            ])
            ->assertSessionHasErrors(['rejection_reason']);
    }

    /** @test */
    public function already_approved_request_cannot_be_rejected(): void
    {
        [$ticket, $fr] = $this->makeFinancialTicket();

        // Approve first
        $this->actingAs($this->admin)->patch(
            route('dashboard.support.financial-requests.approve', $fr),
            ['approved_amount' => '200.00', 'deduction_type' => 'advance', 'is_benefit' => '0', 'settlement_month' => now()->month]
        );

        // Attempt to reject — should redirect with flash error, no status change
        $this->actingAs($this->admin)->patch(
            route('dashboard.support.financial-requests.reject', $fr),
            ['rejection_reason' => 'Trying to reject an approved request.']
        );

        $fr->refresh();
        $this->assertEquals(FinancialRequestStatus::Approved, $fr->status);
    }

    // ── Cancel ────────────────────────────────────────────────────────────────

    /** @test */
    public function super_admin_can_cancel_a_pending_entry(): void
    {
        [$ticket, $fr] = $this->makeFinancialTicket();

        // Approve to create the entry
        $this->actingAs($this->superAdmin)->patch(
            route('dashboard.support.financial-requests.approve', $fr),
            ['approved_amount' => '200.00', 'deduction_type' => 'advance', 'is_benefit' => '0', 'settlement_month' => now()->month]
        );

        $entry = PendingFinancialEntry::where('financial_request_id', $fr->id)->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->patch(route('dashboard.support.pending-entries.cancel', $entry), [
                'cancel_reason' => 'Wrong amount entered.',
            ])
            ->assertRedirect();

        $entry->refresh();
        $this->assertEquals(PendingEntryStatus::Cancelled, $entry->status);
    }

    /** @test */
    public function regular_admin_cannot_cancel_a_pending_entry(): void
    {
        [$ticket, $fr] = $this->makeFinancialTicket();

        $this->actingAs($this->superAdmin)->patch(
            route('dashboard.support.financial-requests.approve', $fr),
            ['approved_amount' => '200.00', 'deduction_type' => 'advance', 'is_benefit' => '0', 'settlement_month' => now()->month]
        );

        $entry = PendingFinancialEntry::where('financial_request_id', $fr->id)->firstOrFail();

        $this->actingAs($this->admin)
            ->patch(route('dashboard.support.pending-entries.cancel', $entry), [
                'cancel_reason' => 'Trying to cancel.',
            ])
            ->assertStatus(403);
    }

    /** @test */
    public function cancel_resets_financial_request_to_pending(): void
    {
        [$ticket, $fr] = $this->makeFinancialTicket();

        $this->actingAs($this->superAdmin)->patch(
            route('dashboard.support.financial-requests.approve', $fr),
            ['approved_amount' => '200.00', 'deduction_type' => 'advance', 'is_benefit' => '0', 'settlement_month' => now()->month]
        );

        $entry = PendingFinancialEntry::where('financial_request_id', $fr->id)->firstOrFail();

        $this->actingAs($this->superAdmin)->patch(
            route('dashboard.support.pending-entries.cancel', $entry),
            ['cancel_reason' => 'Correcting the amount.']
        );

        $fr->refresh();
        $this->assertEquals(FinancialRequestStatus::Pending, $fr->status);
        $this->assertNull($fr->approved_amount);
    }

    /** @test */
    public function re_approval_after_cancel_creates_one_entry_total(): void
    {
        [$ticket, $fr] = $this->makeFinancialTicket();

        $approvePayload = [
            'approved_amount'   => '200.00',
            'deduction_type'    => 'advance',
            'is_benefit'        => '0',
            'settlement_month'  => now()->month,
        ];

        // First approval
        $this->actingAs($this->superAdmin)->patch(
            route('dashboard.support.financial-requests.approve', $fr), $approvePayload
        );
        $entry = PendingFinancialEntry::where('financial_request_id', $fr->id)->firstOrFail();

        // Cancel
        $this->actingAs($this->superAdmin)->patch(
            route('dashboard.support.pending-entries.cancel', $entry),
            ['cancel_reason' => 'Wrong amount.']
        );

        // Re-approve with corrected amount
        $approvePayload['approved_amount'] = '350.00';
        $this->actingAs($this->superAdmin)->patch(
            route('dashboard.support.financial-requests.approve', $fr), $approvePayload
        );

        // DB still has only one entry (re-used the cancelled row)
        $this->assertDatabaseCount('pending_financial_entries', 1);

        $entry->refresh();
        $this->assertEquals(PendingEntryStatus::Pending, $entry->status);
        $this->assertEquals('350.00', number_format((float) $entry->amount, 2));
    }

    // ── Board page ────────────────────────────────────────────────────────────

    /** @test */
    public function financial_requests_board_loads_for_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard.support.financial-requests.index'))
            ->assertStatus(200);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeAdmin(bool $isSuperAdmin = false): User
    {
        // Create (or find) a role that has the 'support' permission
        $roleId = DB::table('roles')->insertGetId([
            'name'        => 'test_role_' . uniqid(),
            'permissions' => json_encode(['support']),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $id = DB::table('users')->insertGetId([
            'name'           => $isSuperAdmin ? 'Super Admin' : 'Regular Admin',
            'email'          => ($isSuperAdmin ? 'super' : 'admin') . '_' . uniqid() . '@test.com',
            'password'       => bcrypt('password'),
            'role_id'        => $roleId,
            'is_super_admin' => $isSuperAdmin,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        return User::findOrFail($id);
    }

    private function makeDelegate(): Delegate
    {
        $id = DB::table('delegates')->insertGetId([
            'name'               => 'Test Delegate',
            'national_id'        => '55555555',
            'portal_enabled'     => true,
            'portal_first_login' => false,
            'status'             => 'active',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
        return Delegate::findOrFail($id);
    }

    private function makePeriod(): MonthlyPeriod
    {
        $id = DB::table('monthly_periods')->insertGetId([
            'year'       => now()->year,
            'month'      => now()->month,
            'label'      => now()->format('F Y'),
            'status'     => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return MonthlyPeriod::findOrFail($id);
    }

    /** Returns [$ticket, $financialRequest] */
    private function makeFinancialTicket(): array
    {
        $ticketId = DB::table('support_tickets')->insertGetId([
            'ticket_number'    => 'TK-' . now()->year . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'platform'         => 'hungerstation',
            'source'           => 'portal',
            'delegate_id'      => $this->delegate->id,
            'category'         => 'advance_request',
            'priority'         => 'normal',
            'subject'          => 'Advance Request Test',
            'status'           => 'open',
            'opened_at'        => now(),
            'last_activity_at' => now(),
            'created_by'       => $this->delegate->id,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $ticket = SupportTicket::findOrFail($ticketId);

        $frId = DB::table('financial_requests')->insertGetId([
            'ticket_id'        => $ticket->id,
            'delegate_id'      => $this->delegate->id,
            'request_category' => 'advance_request',
            'requested_amount' => 500.00,
            'requested_notes'  => 'I need an advance.',
            'status'           => 'pending',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $fr = FinancialRequest::findOrFail($frId);

        return [$ticket, $fr];
    }
}
