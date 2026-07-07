<?php
namespace App\Services;

use App\Enums\PeriodAction;
use App\Models\AuditLog;
use App\Models\Delegate;
use App\Models\DelegateDocument;
use App\Models\DelegatePlatformAssignment;
use App\Models\MonthlyPeriod;
use App\Models\MonthlyPeriodLog;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use App\Models\VehicleDocument;
use App\Models\VehicleRental;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AuditService
{
    public function log(
        string $action,
        ?Model $model = null,
        array $oldValues = [],
        array $newValues = [],
        ?string $description = null
    ): AuditLog {
        $userId = null;
        $userType = null;

        if (auth('web')->check()) {
            $userId = auth('web')->id();
            $userType = 'admin';
        } elseif (auth('delegate')->check()) {
            $userId = auth('delegate')->id();
            $userType = 'delegate';
        }

        return AuditLog::create([
            'user_id'     => $userId,
            'user_type'   => $userType,
            'action'      => $action,
            'model_type'  => $model ? get_class($model) : null,
            'model_id'    => $model?->getKey(),
            'old_values'  => $oldValues ?: null,
            'new_values'  => $newValues ?: null,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'created_at'  => now(),
        ]);
    }

    public function getTimelineForDelegate(int $delegateId, int $limit = 30): Collection
    {
        return AuditLog::where(function ($q) use ($delegateId) {
                $q->where('model_type', Delegate::class)
                  ->where('model_id', $delegateId);
            })
            ->orWhere(function ($q) use ($delegateId) {
                $q->whereIn('model_type', [
                    DelegateDocument::class,
                    DelegatePlatformAssignment::class,
                    VehicleAssignment::class,
                ])
                ->where(function ($inner) use ($delegateId) {
                    $inner->whereRaw("JSON_EXTRACT(new_values, '$.delegate_id') = ?", [$delegateId])
                          ->orWhereRaw("JSON_EXTRACT(old_values, '$.delegate_id') = ?", [$delegateId]);
                });
            })
            ->with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getTimelineForVehicle(int $vehicleId, int $limit = 30): Collection
    {
        return AuditLog::where(function ($q) use ($vehicleId) {
                $q->where('model_type', Vehicle::class)
                  ->where('model_id', $vehicleId);
            })
            ->orWhere(function ($q) use ($vehicleId) {
                $q->whereIn('model_type', [
                    VehicleDocument::class,
                    VehicleAssignment::class,
                    VehicleRental::class,
                ])
                ->where(function ($inner) use ($vehicleId) {
                    $inner->whereRaw("JSON_EXTRACT(new_values, '$.vehicle_id') = ?", [$vehicleId])
                          ->orWhereRaw("JSON_EXTRACT(old_values, '$.vehicle_id') = ?", [$vehicleId]);
                });
            })
            ->with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function logPeriodAction(
        MonthlyPeriod $period,
        PeriodAction $action,
        string $fromStatus,
        string $toStatus,
        ?string $reason = null
    ): void {
        DB::transaction(function () use ($period, $action, $fromStatus, $toStatus, $reason) {
            $userId = auth('web')->id();

            MonthlyPeriodLog::create([
                'monthly_period_id' => $period->id,
                'action'            => $action,
                'from_status'       => $fromStatus,
                'to_status'         => $toStatus,
                'performed_by'      => $userId,
                'reason'            => $reason,
            ]);

            $this->log(
                $action->value,
                $period,
                ['status' => $fromStatus],
                ['status' => $toStatus],
                $reason
            );
        });
    }
}
