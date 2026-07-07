<?php

namespace App\Traits;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            app(AuditService::class)->log($model->getAuditAction('created'), $model, [], $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $ignored = $model->getAuditIgnoredColumns();
            $old     = collect($model->getOriginal())->except($ignored)->toArray();
            $new     = collect($model->getChanges())->except($ignored)->toArray();

            if (!empty($new)) {
                app(AuditService::class)->log($model->getAuditAction('updated'), $model, $old, $new);
            }
        });

        static::deleted(function (Model $model) {
            app(AuditService::class)->log($model->getAuditAction('deleted'), $model, $model->getAttributes(), []);
        });
    }

    protected function getAuditIgnoredColumns(): array
    {
        return ['updated_at', 'remember_token'];
    }

    protected function getAuditAction(string $event): string
    {
        return $event;
    }
}
