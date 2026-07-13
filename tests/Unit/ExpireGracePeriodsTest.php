<?php

namespace Tests\Unit;

use App\Console\Commands\ExpireTicketGracePeriods;
use App\Enums\TicketStatus;
use App\Models\SupportTicket;
use App\Services\Support\AdminActivityLogger;
use App\Services\Support\SlaCalculator;
use App\Services\Support\SlaPolicy;
use App\Services\Support\TicketAuditLogger;
use App\Services\Support\TicketLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExpireGracePeriodsTest extends TestCase
{
    use RefreshDatabase;

    private TicketLifecycleService $lifecycleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lifecycleService = new TicketLifecycleService(
            slaCalculator: new SlaCalculator(new \App\Models\SlaPolicy()),
            auditLogger: new TicketAuditLogger(),
            activityLogger: new AdminActivityLogger(),
        );
    }

    /** @test */
    public function it_closes_resolved_ticket_with_expired_grace_deadline(): void
    {
        $ticket = $this->makeResolvedTicket(gracePast: true);

        $this->artisan(ExpireTicketGracePeriods::class)
            ->assertExitCode(0);

        $ticket->refresh();
        $this->assertEquals(TicketStatus::Closed->value, $ticket->status->value);
        $this->assertNotNull($ticket->permanently_closed_at);
    }

    /** @test */
    public function it_leaves_resolved_ticket_with_future_grace_deadline_unchanged(): void
    {
        $ticket = $this->makeResolvedTicket(gracePast: false);

        $this->artisan(ExpireTicketGracePeriods::class)
            ->assertExitCode(0);

        $ticket->refresh();
        $this->assertEquals(TicketStatus::Resolved->value, $ticket->status->value);
        $this->assertNull($ticket->permanently_closed_at);
    }

    /** @test */
    public function it_leaves_open_tickets_unchanged(): void
    {
        $ticket = $this->makeTicketWithStatus(TicketStatus::Open);

        $this->artisan(ExpireTicketGracePeriods::class)
            ->assertExitCode(0);

        $ticket->refresh();
        $this->assertEquals(TicketStatus::Open->value, $ticket->status->value);
    }

    /** @test */
    public function it_leaves_already_closed_tickets_unchanged(): void
    {
        $ticket = $this->makeTicketWithStatus(TicketStatus::Closed, permanently: true);

        $closedAt = $ticket->permanently_closed_at;

        $this->artisan(ExpireTicketGracePeriods::class)
            ->assertExitCode(0);

        $ticket->refresh();
        $this->assertEquals(TicketStatus::Closed->value, $ticket->status->value);
        $this->assertEquals($closedAt->toDateTimeString(), $ticket->permanently_closed_at->toDateTimeString());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeResolvedTicket(bool $gracePast): SupportTicket
    {
        return $this->insertTicket([
            'status'              => TicketStatus::Resolved->value,
            'close_grace_deadline' => $gracePast
                ? now()->subHour()
                : now()->addHours(24),
            'resolved_at'        => now()->subHours(2),
        ]);
    }

    private function makeTicketWithStatus(TicketStatus $status, bool $permanently = false): SupportTicket
    {
        return $this->insertTicket([
            'status'               => $status->value,
            'permanently_closed_at' => $permanently ? now()->subDay() : null,
            'closed_at'            => $permanently ? now()->subDay() : null,
        ]);
    }

    private function insertTicket(array $overrides): SupportTicket
    {
        $id = DB::table('support_tickets')->insertGetId(array_merge([
            'ticket_number'    => 'TK-' . now()->year . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'platform'         => 'hungerstation',
            'source'           => 'portal',
            'delegate_id'      => 1,
            'category'         => 'general_inquiry',
            'priority'         => 'normal',
            'subject'          => 'Test',
            'status'           => TicketStatus::Open->value,
            'opened_at'        => now()->subDay(),
            'last_activity_at' => now(),
            'created_by'       => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ], $overrides));

        return SupportTicket::findOrFail($id);
    }
}
