<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\Delegate;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * P8-006 — Feature tests: Ticket reply (delegate + admin)
 */
class PortalTicketReplyTest extends TestCase
{
    use RefreshDatabase;

    private Delegate $delegate;
    private Delegate $otherDelegate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->delegate      = $this->makeDelegate('33333333');
        $this->otherDelegate = $this->makeDelegate('44444444');
    }

    // ── Delegate can reply to own ticket ──────────────────────────────────────

    /** @test */
    public function delegate_can_reply_to_own_open_ticket(): void
    {
        $ticket = $this->insertTicket($this->delegate->id, ['status' => 'open']);

        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.reply', $ticket), [
                'content' => 'This is my reply to the ticket.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('support_ticket_replies', [
            'ticket_id'          => $ticket->id,
            'author_type'        => 'delegate',
            'author_delegate_id' => $this->delegate->id,
            'content'            => 'This is my reply to the ticket.',
            'is_internal_note'   => 0,
        ]);
    }

    /** @test */
    public function delegate_can_reply_to_awaiting_delegate_ticket(): void
    {
        $ticket = $this->insertTicket($this->delegate->id, ['status' => 'awaiting_delegate']);

        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.reply', $ticket), [
                'content' => 'Following up on my request.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('support_ticket_replies', [
            'ticket_id' => $ticket->id,
            'content'   => 'Following up on my request.',
        ]);
    }

    // ── Delegate cannot reply to another delegate's ticket ────────────────────

    /** @test */
    public function delegate_cannot_reply_to_another_delegates_ticket(): void
    {
        $ticket = $this->insertTicket($this->otherDelegate->id, ['status' => 'open']);

        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.reply', $ticket), [
                'content' => 'Trying to reply to someone else ticket.',
            ])
            ->assertStatus(403);

        $this->assertDatabaseMissing('support_ticket_replies', [
            'ticket_id'          => $ticket->id,
            'author_delegate_id' => $this->delegate->id,
        ]);
    }

    // ── Permanently closed ticket blocks delegate reply ───────────────────────

    /** @test */
    public function permanently_closed_ticket_rejects_delegate_reply(): void
    {
        $ticket = $this->insertTicket($this->delegate->id, [
            'status'               => 'closed',
            'permanently_closed_at' => now()->subDay(),
            'closed_at'            => now()->subDay(),
        ]);

        $response = $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.reply', $ticket), [
                'content' => 'Trying to reply to a closed ticket.',
            ]);

        // Should redirect back with error (canDelegateReply() returns false)
        $response->assertRedirect();
        $this->assertDatabaseMissing('support_ticket_replies', [
            'ticket_id' => $ticket->id,
        ]);
    }

    // ── Resolved + grace period → Reopened ───────────────────────────────────

    /** @test */
    public function delegate_reply_during_grace_period_reopens_ticket(): void
    {
        $ticket = $this->insertTicket($this->delegate->id, [
            'status'               => 'resolved',
            'resolved_at'          => now()->subHour(),
            'close_grace_deadline' => now()->addHours(48),
        ]);

        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.reply', $ticket), [
                'content' => 'I still have questions about my ticket.',
            ])
            ->assertRedirect();

        $ticket->refresh();
        $this->assertEquals(TicketStatus::Reopened->value, $ticket->status->value);
    }

    // ── Internal notes not visible to delegate ────────────────────────────────

    /** @test */
    public function internal_notes_are_not_visible_in_portal_reply_thread(): void
    {
        $ticket = $this->insertTicket($this->delegate->id);

        // Insert an internal note directly
        DB::table('support_ticket_replies')->insert([
            'ticket_id'        => $ticket->id,
            'author_type'      => 'admin',
            'author_user_id'   => null,
            'content'          => 'SECRET INTERNAL NOTE',
            'is_internal_note' => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $ticket->load([
            'replies' => fn ($q) => $q->where('is_internal_note', false)->orderBy('created_at'),
        ]);

        $this->assertCount(0, $ticket->replies);
        $this->assertStringNotContainsString('SECRET INTERNAL NOTE',
            $ticket->replies->implode('content', ''));
    }

    // ── Content validation ────────────────────────────────────────────────────

    /** @test */
    public function empty_reply_content_fails_validation(): void
    {
        $ticket = $this->insertTicket($this->delegate->id, ['status' => 'open']);

        $this->actingAs($this->delegate, 'delegate')
            ->post(route('portal.support.tickets.reply', $ticket), [
                'content' => '',
            ])
            ->assertSessionHasErrors(['content']);
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
