<?php

namespace Tests\Unit;

use App\Enums\TicketPriority;
use App\Models\SlaPolicy;
use App\Models\SupportTicket;
use App\Services\Support\SlaCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlaCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private SlaCalculator $calculator;

    private Carbon $fixedOpenedAt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new SlaCalculator(new SlaPolicy());

        // Fixed timestamp so tests are deterministic and never flaky
        $this->fixedOpenedAt = Carbon::parse('2026-07-11 08:00:00');

        $this->seedSlaPolicies();
    }

    // ── applyToTicket() ───────────────────────────────────────────────────────

    /** @test */
    public function apply_to_ticket_sets_correct_deadlines_for_urgent(): void
    {
        $ticket = $this->makeTicket(TicketPriority::Urgent, $this->fixedOpenedAt);

        $this->calculator->applyToTicket($ticket);

        // urgent: first_response=2h, resolution=8h (from SlaPolicySeeder)
        $this->assertEquals(
            $this->fixedOpenedAt->copy()->addHours(2)->toDateTimeString(),
            $ticket->sla_first_response_deadline->toDateTimeString(),
        );
        $this->assertEquals(
            $this->fixedOpenedAt->copy()->addHours(8)->toDateTimeString(),
            $ticket->sla_resolution_deadline->toDateTimeString(),
        );
    }

    /** @test */
    public function apply_to_ticket_sets_correct_deadlines_for_high(): void
    {
        $ticket = $this->makeTicket(TicketPriority::High, $this->fixedOpenedAt);

        $this->calculator->applyToTicket($ticket);

        $this->assertEquals(
            $this->fixedOpenedAt->copy()->addHours(4)->toDateTimeString(),
            $ticket->sla_first_response_deadline->toDateTimeString(),
        );
        $this->assertEquals(
            $this->fixedOpenedAt->copy()->addHours(24)->toDateTimeString(),
            $ticket->sla_resolution_deadline->toDateTimeString(),
        );
    }

    /** @test */
    public function apply_to_ticket_sets_correct_deadlines_for_normal(): void
    {
        $ticket = $this->makeTicket(TicketPriority::Normal, $this->fixedOpenedAt);

        $this->calculator->applyToTicket($ticket);

        $this->assertEquals(
            $this->fixedOpenedAt->copy()->addHours(8)->toDateTimeString(),
            $ticket->sla_first_response_deadline->toDateTimeString(),
        );
        $this->assertEquals(
            $this->fixedOpenedAt->copy()->addHours(48)->toDateTimeString(),
            $ticket->sla_resolution_deadline->toDateTimeString(),
        );
    }

    /** @test */
    public function apply_to_ticket_sets_correct_deadlines_for_low(): void
    {
        $ticket = $this->makeTicket(TicketPriority::Low, $this->fixedOpenedAt);

        $this->calculator->applyToTicket($ticket);

        $this->assertEquals(
            $this->fixedOpenedAt->copy()->addHours(24)->toDateTimeString(),
            $ticket->sla_first_response_deadline->toDateTimeString(),
        );
        $this->assertEquals(
            $this->fixedOpenedAt->copy()->addHours(96)->toDateTimeString(),
            $ticket->sla_resolution_deadline->toDateTimeString(),
        );
    }

    // ── evaluateFirstResponse() ───────────────────────────────────────────────

    /** @test */
    public function evaluate_first_response_returns_1_when_reply_is_on_time(): void
    {
        $ticket = $this->makeTicket(TicketPriority::Normal, $this->fixedOpenedAt);
        $this->calculator->applyToTicket($ticket);

        // Reply arrives 1 hour before deadline
        $ticket->first_reply_at = $ticket->sla_first_response_deadline->copy()->subHour();

        $this->calculator->evaluateFirstResponse($ticket);

        $this->assertSame(1, $ticket->sla_first_response_met);
    }

    /** @test */
    public function evaluate_first_response_returns_0_when_reply_is_late(): void
    {
        $ticket = $this->makeTicket(TicketPriority::Normal, $this->fixedOpenedAt);
        $this->calculator->applyToTicket($ticket);

        // Reply arrives 1 hour after deadline
        $ticket->first_reply_at = $ticket->sla_first_response_deadline->copy()->addHour();

        $this->calculator->evaluateFirstResponse($ticket);

        $this->assertSame(0, $ticket->sla_first_response_met);
    }

    /** @test */
    public function evaluate_first_response_returns_null_when_no_reply_yet(): void
    {
        $ticket = $this->makeTicket(TicketPriority::Normal, $this->fixedOpenedAt);
        $this->calculator->applyToTicket($ticket);
        $ticket->first_reply_at = null;

        $this->calculator->evaluateFirstResponse($ticket);

        $this->assertNull($ticket->sla_first_response_met);
    }

    // ── evaluateResolution() ──────────────────────────────────────────────────

    /** @test */
    public function evaluate_resolution_returns_1_when_resolved_on_time(): void
    {
        $ticket = $this->makeTicket(TicketPriority::Normal, $this->fixedOpenedAt);
        $this->calculator->applyToTicket($ticket);
        $ticket->resolved_at = $ticket->sla_resolution_deadline->copy()->subHour();

        $this->calculator->evaluateResolution($ticket);

        $this->assertSame(1, $ticket->sla_resolution_met);
    }

    /** @test */
    public function evaluate_resolution_returns_0_when_resolved_late(): void
    {
        $ticket = $this->makeTicket(TicketPriority::Normal, $this->fixedOpenedAt);
        $this->calculator->applyToTicket($ticket);
        $ticket->resolved_at = $ticket->sla_resolution_deadline->copy()->addHour();

        $this->calculator->evaluateResolution($ticket);

        $this->assertSame(0, $ticket->sla_resolution_met);
    }

    /** @test */
    public function evaluate_resolution_returns_null_when_not_resolved(): void
    {
        $ticket = $this->makeTicket(TicketPriority::Normal, $this->fixedOpenedAt);
        $this->calculator->applyToTicket($ticket);
        $ticket->resolved_at = null;

        $this->calculator->evaluateResolution($ticket);

        $this->assertNull($ticket->sla_resolution_met);
    }

    // ── recalculate() ─────────────────────────────────────────────────────────

    /** @test */
    public function recalculate_updates_deadlines_after_priority_change(): void
    {
        $ticket = $this->makeTicket(TicketPriority::Low, $this->fixedOpenedAt);
        $this->calculator->applyToTicket($ticket);

        $originalResolutionDeadline = $ticket->sla_resolution_deadline->copy();

        // Upgrade priority to urgent
        $ticket->priority = TicketPriority::Urgent;
        $this->calculator->recalculate($ticket);

        // Urgent deadline is 8h vs low's 96h — must be earlier
        $this->assertTrue($ticket->sla_resolution_deadline->lt($originalResolutionDeadline));
    }

    /** @test */
    public function recalculate_re_evaluates_outcome_if_timestamps_present(): void
    {
        // Ticket opened at fixed time, priority low, reply sent on time for low
        $ticket = $this->makeTicket(TicketPriority::Low, $this->fixedOpenedAt);
        $this->calculator->applyToTicket($ticket);

        // Reply sent 12 hours after open — within low's 24h window
        $ticket->first_reply_at = $this->fixedOpenedAt->copy()->addHours(12);
        $this->calculator->evaluateFirstResponse($ticket);
        $this->assertSame(1, $ticket->sla_first_response_met);

        // Now upgrade to urgent (first response deadline: 2h)
        // 12h reply is now LATE for urgent
        $ticket->priority = TicketPriority::Urgent;
        $this->calculator->recalculate($ticket);

        $this->assertSame(0, $ticket->sla_first_response_met);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeTicket(TicketPriority $priority, Carbon $openedAt): SupportTicket
    {
        $ticket = new SupportTicket();
        $ticket->priority  = $priority;
        $ticket->opened_at = $openedAt->copy();
        return $ticket;
    }

    private function seedSlaPolicies(): void
    {
        $policies = [
            ['priority' => 'urgent', 'first_response_hours' => 2,  'resolution_hours' => 8],
            ['priority' => 'high',   'first_response_hours' => 4,  'resolution_hours' => 24],
            ['priority' => 'normal', 'first_response_hours' => 8,  'resolution_hours' => 48],
            ['priority' => 'low',    'first_response_hours' => 24, 'resolution_hours' => 96],
        ];

        foreach ($policies as $policy) {
            \Illuminate\Support\Facades\DB::table('sla_policies')->updateOrInsert(
                ['priority' => $policy['priority']],
                array_merge($policy, ['created_at' => now(), 'updated_at' => now()]),
            );
        }
    }
}
