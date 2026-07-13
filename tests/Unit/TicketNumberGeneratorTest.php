<?php

namespace Tests\Unit;

use App\Services\Support\TicketNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private TicketNumberGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new TicketNumberGenerator(DB::connection());
    }

    /** @test */
    public function it_returns_00001_when_no_tickets_exist_for_the_year(): void
    {
        DB::table('support_tickets')->delete();

        $number = DB::transaction(fn () => $this->generator->generate(2026));

        $this->assertSame('TK-2026-00001', $number);
    }

    /** @test */
    public function it_increments_correctly_when_one_ticket_exists(): void
    {
        DB::table('support_tickets')->delete();
        $this->insertTicketWithNumber('TK-2026-00001');

        $number = DB::transaction(fn () => $this->generator->generate(2026));

        $this->assertSame('TK-2026-00002', $number);
    }

    /** @test */
    public function it_resets_sequence_on_year_change(): void
    {
        DB::table('support_tickets')->delete();
        $this->insertTicketWithNumber('TK-2025-00099');

        $number = DB::transaction(fn () => $this->generator->generate(2026));

        $this->assertSame('TK-2026-00001', $number);
    }

    /** @test */
    public function it_continues_beyond_5_digits_when_needed(): void
    {
        DB::table('support_tickets')->delete();
        $this->insertTicketWithNumber('TK-2026-99999');

        $number = DB::transaction(fn () => $this->generator->generate(2026));

        $this->assertSame('TK-2026-100000', $number);
    }

    /** @test */
    public function it_pads_single_digit_numbers_to_five_digits(): void
    {
        DB::table('support_tickets')->delete();

        $number = DB::transaction(fn () => $this->generator->generate(2026));

        $this->assertStringStartsWith('TK-2026-0000', $number);
    }

    /** @test */
    public function is_valid_format_accepts_correct_format(): void
    {
        $this->assertTrue($this->generator->isValidFormat('TK-2026-00001'));
        $this->assertTrue($this->generator->isValidFormat('TK-2026-99999'));
        $this->assertTrue($this->generator->isValidFormat('TK-2026-100000'));
    }

    /** @test */
    public function is_valid_format_rejects_malformed_strings(): void
    {
        $this->assertFalse($this->generator->isValidFormat('TK-26-00001'));
        $this->assertFalse($this->generator->isValidFormat('2026-00001'));
        $this->assertFalse($this->generator->isValidFormat('TK-2026-1'));
        $this->assertFalse($this->generator->isValidFormat(''));
        $this->assertFalse($this->generator->isValidFormat('TICKET-2026-00001'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function insertTicketWithNumber(string $number): void
    {
        DB::table('support_tickets')->insert([
            'ticket_number'   => $number,
            'platform'        => 'hungerstation',
            'source'          => 'portal',
            'delegate_id'     => 1,
            'category'        => 'general_inquiry',
            'priority'        => 'normal',
            'subject'         => 'Test',
            'status'          => 'open',
            'opened_at'       => now(),
            'last_activity_at' => now(),
            'created_by'      => 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }
}
