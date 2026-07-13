<?php

namespace App\Jobs;

use App\Enums\ActivityActorType;
use App\Models\ActivityFeed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Phase 3 — Queued job that persists one activity_feed row.
 *
 * $tries = 1: activity feed is best-effort; a dispatch failure is logged but
 * never retried and never blocks or rolls back the triggering ticket operation.
 */
class WriteActivityFeedEntry implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(
        private readonly string  $platform,
        private readonly string  $actorType,
        private readonly ?int    $actorId,
        private readonly string  $actorLabel,
        private readonly string  $action,
        private readonly ?string $subjectType,
        private readonly ?int    $subjectId,
        private readonly ?string $subjectLabel,
        private readonly string  $description,
        private readonly ?array  $data,
    ) {
        $this->onQueue('activity');
    }

    public function handle(): void
    {
        try {
            $entry = new ActivityFeed();
            $entry->platform      = $this->platform;
            $entry->actor_type    = ActivityActorType::from($this->actorType);
            $entry->actor_id      = $this->actorId;
            $entry->actor_label   = $this->actorLabel;
            $entry->action        = $this->action;
            $entry->subject_type  = $this->subjectType;
            $entry->subject_id    = $this->subjectId;
            $entry->subject_label = $this->subjectLabel;
            $entry->description   = $this->description;
            $entry->data          = $this->data;
            $entry->created_at    = now();
            $entry->save();
        } catch (\Throwable $e) {
            Log::warning('WriteActivityFeedEntry failed', [
                'action'      => $this->action,
                'actor_label' => $this->actorLabel,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
