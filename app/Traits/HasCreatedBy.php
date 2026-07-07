<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasCreatedBy
{
    public static function bootHasCreatedBy(): void
    {
        static::creating(function (Model $model) {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function (Model $model) {
            if (auth()->check() && $model->isFillable('updated_by')) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
