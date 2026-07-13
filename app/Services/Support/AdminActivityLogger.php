<?php

namespace App\Services\Support;

use App\Enums\ActivityActorType;
use App\Jobs\WriteActivityFeedEntry;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;

/**
 * Phase 3 — Global admin activity feed writer.
 *
 * Dispatches WriteActivityFeedEntry on the 'activity' queue — never blocks.
 * A dispatch failure is caught and logged; it never propagates to the caller.
 */
class AdminActivityLogger
{
    /**
     * Log an admin-performed action.
     *
     * @param  Authenticatable $admin
     * @param  string          $action       Slug, e.g. ticket_assigned
     * @param  string          $description  Pre-rendered English sentence
     * @param  object|null     $subject      Eloquent model (optional)
     * @param  string|null     $subjectLabel Cached human label, e.g. TK-2026-00042
     * @param  array           $data
     * @param  string          $platform
     */
    public function logAdmin(
        Authenticatable $admin,
        string $action,
        string $description,
        ?object $subject = null,
        ?string $subjectLabel = null,
        array $data = [],
        string $platform = 'hungerstation',
    ): void {
        $label = $admin->name ?? (string) $admin->getAuthIdentifier();

        $this->dispatch(
            platform: $platform,
            actorType: ActivityActorType::Admin,
            actorId: (int) $admin->getAuthIdentifier(),
            actorLabel: $label,
            action: $action,
            description: $description,
            subject: $subject,
            subjectLabel: $subjectLabel,
            data: $data,
        );
    }

    /**
     * Log a delegate action visible in the admin feed.
     *
     * @param  int         $delegateId
     * @param  string      $delegateLabel
     * @param  string      $action
     * @param  string      $description
     * @param  object|null $subject
     * @param  string|null $subjectLabel
     * @param  array       $data
     * @param  string      $platform
     */
    public function logDelegate(
        int $delegateId,
        string $delegateLabel,
        string $action,
        string $description,
        ?object $subject = null,
        ?string $subjectLabel = null,
        array $data = [],
        string $platform = 'hungerstation',
    ): void {
        $this->dispatch(
            platform: $platform,
            actorType: ActivityActorType::Delegate,
            actorId: $delegateId,
            actorLabel: $delegateLabel,
            action: $action,
            description: $description,
            subject: $subject,
            subjectLabel: $subjectLabel,
            data: $data,
        );
    }

    /**
     * Log a system-generated event (no human actor).
     */
    public function logSystem(
        string $action,
        string $description,
        ?object $subject = null,
        ?string $subjectLabel = null,
        array $data = [],
        string $platform = 'hungerstation',
    ): void {
        $this->dispatch(
            platform: $platform,
            actorType: ActivityActorType::System,
            actorId: null,
            actorLabel: 'system',
            action: $action,
            description: $description,
            subject: $subject,
            subjectLabel: $subjectLabel,
            data: $data,
        );
    }

    private function dispatch(
        string $platform,
        ActivityActorType $actorType,
        ?int $actorId,
        string $actorLabel,
        string $action,
        string $description,
        ?object $subject,
        ?string $subjectLabel,
        array $data,
    ): void {
        try {
            dispatch(new WriteActivityFeedEntry(
                platform: $platform,
                actorType: $actorType->value,
                actorId: $actorId,
                actorLabel: $actorLabel,
                action: $action,
                subjectType: $subject ? get_class($subject) : null,
                subjectId: $subject?->id ?? null,
                subjectLabel: $subjectLabel,
                description: $description,
                data: $data ?: null,
            ));
        } catch (\Throwable $e) {
            Log::warning('AdminActivityLogger dispatch failed', [
                'action' => $action,
                'error'  => $e->getMessage(),
            ]);
        }
    }
}
