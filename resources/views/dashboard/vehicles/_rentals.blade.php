<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ __('dashboard.vehicle_rentals') }}</h4>
        <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
        <div class="heading-elements">
            <ul class="mb-0 list-inline">
                <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                <li><a data-action="close"><i class="ft-x"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="card-content collapse show">
        <div class="card-body">

            @php
                $latestRentals = $vehicle->vehicleRentals()
                    ->with(['monthlyPeriod', 'delegate'])
                    ->orderByDesc('created_at')
                    ->limit(3)
                    ->get();
            @endphp

            @forelse ($latestRentals as $rental)
                <div class="d-flex justify-content-between align-items-center mb-1 pb-1"
                     style="border-bottom:1px solid #f0f0f0;">
                    <div>
                        <span class="font-weight-bold">
                            {{ $rental->monthlyPeriod ? $rental->monthlyPeriod->year . '/' . str_pad($rental->monthlyPeriod->month, 2, '0', STR_PAD_LEFT) : '—' }}
                        </span>
                        <span class="text-muted mr-2">{{ number_format($rental->amount, 2) }} ر.س</span>
                    </div>
                    <div>
                        @if ($rental->payment_by->value === 'company')
                            <span class="badge badge-secondary">الشركة</span>
                        @else
                            <span class="badge badge-primary">{{ $rental->delegate?->name ?? 'مندوب' }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted text-center py-2">لا توجد إيجارات</p>
            @endforelse

            <div class="text-center mt-2">
                @can('update', $vehicle)
                    <a href="{{ route('dashboard.vehicles.rentals.create', $vehicle) }}"
                       class="btn btn-sm btn-success mr-1">
                        <i class="la la-plus"></i> إضافة إيجار
                    </a>
                @endcan
                <a href="{{ route('dashboard.vehicles.rentals.index', $vehicle) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i class="la la-list"></i> عرض الكل
                </a>
            </div>

        </div>
    </div>
</div>
