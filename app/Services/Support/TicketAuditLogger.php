<?php

namespace App\Services\Support;

use App\Enums\ActivityActorType;
use App\Models\SupportTicket;
use App\Models\TicketAuditLog;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Phase 3 — Per-ticket audit timeline writer.
 *
 * Appends structured entries to ticket_audit_logs for every state transition
 * or significant event on a SupportTicket. Rows are append-only.
 *
 * Distinct from AdminActivityLogger (global admin feed).
 * Both logger services are called from ticket controller actions (Stage B).
 */
class TicketAuditLogger
{
    /**
     * Record an admin action on a ticket.
     *
     * @param  SupportTicket   $ticket
     * @param  Authenticatable $admin
     * @param  string          $action       E.g. ticket_assigned, status_changed
     * @param  string          $description  Human-readable sentence
     * @param  string|null     $fromValue    Previous value (for transitions)
     * @param  string|null     $toValue      New value (for transitions)
     * @param  array           $data         Arbitrary metadata
     */
    public function logAdmin(
        SupportTicket $ticket,
        Authenticatable $admin,
        string $action,
        string $description,
        ?string $fromValue = null,
        ?string $toValue = null,
        array $data = [],
    ): TicketAuditLog {
        $label = method_exists($admin, 'name') ? $admin->name : (string) $admin->getAuthIdentifier();

        return $this->write(
            ticket: $ticket,
            actorType: ActivityActorType::Admin,
            actorId: (int) $admin->getAuthIdentifier(),
            actorLabel: $label,
            action: $action,
            description: $description,
            fromValue: $fromValue,
            toValue: $toValue,
            data: $data,
        );
    }

    /**
     * Record a delegate action on a ticket.
     *
     * @param  SupportTicket $ticket
     * @param  int           $delegateId
     * @param  string        $delegateLabel  Cached display name at write time
     * @param  string        $action
     * @param  string        $description
     * @param  string|null   $fromValue
     * @param  string|null   $toValue
     * @param  array         $data
     */
    public function logDelegate(
        SupportTicket $ticket,
        int $delegateId,
        string $delegateLabel,
        string $action,
        string $description,
        ?string $fromValue = null,
        ?string $toValue = null,
        array $data = [],
    ): TicketAuditLog {
        return $this->write(
            ticket: $ticket,
            actorType: ActivityActorType::Delegate,
            actorId: $delegateId,
            actorLabel: $delegateLabel,
            action: $action,
            description: $description,
            fromValue: $fromValue,
            toValue: $toValue,
            data: $data,
        );
    }

    /**
     * Record a system-generated event on a ticket (no human actor).
     *
     * @param  SupportTicket $ticket
     * @param  string        $action
     * @param  string        $description
     * @param  string|null   $fromValue
     * @param  string|null   $toValue
     * @param  array         $data
     */
    public function logSystem(
        SupportTicket $ticket,
        string $action,
        string $description,
        ?string $fromValue = null,
        ?string $toValue = null,
        array $data = [],
    ): TicketAuditLog {
        return $this->write(
            ticket: $ticket,
            actorType: ActivityActorType::System,
            actorId: null,
            actorLabel: 'system',
            action: $action,
            description: $description,
            fromValue: $fromValue,
            toValue: $toValue,
            data: $data,
        );
    }

    /**
     * Write the audit log row.
     */
    private function write(
        SupportTicket $ticket,
        ActivityActorType $actorType,
        ?int $actorId,
        string $actorLabel,
        string $action,
        string $description,
        ?string $fromValue,
        ?string $toValue,
        array $data,
    ): TicketAuditLog {
        $log = new TicketAuditLog();
        $log->ticket_id   = $ticket->id;
        $log->actor_type  = $actorType;
        $log->actor_id    = $actorId;
        $log->actor_label = $actorLabel;
        $log->action      = $action;
        $log->from_value  = $fromValue;
        $log->to_value    = $toValue;
        $log->description = $description;
        $log->data        = $data ?: null;
        $log->created_at  = now();
        $log->save();

        return $log;
    }
}
