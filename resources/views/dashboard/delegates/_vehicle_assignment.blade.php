<div class="card">
    <div class="card-header">
        <h4 class="card-title">المركبة المعينة</h4>
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
                $assignmentService  = app(\App\Services\Dashboard\Vehicles\IVehicleAssignmentService::class);
                $delegateAssignments = $assignmentService->getForDelegate($delegate->id);
                $activeAssignment   = $delegateAssignments->where('is_active', true)->first();
            @endphp

            @if ($activeAssignment)
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="la la-car font-large-2 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">
                            <a href="{{ route('dashboard.vehicles.show', $activeAssignment->vehicle) }}">
                                {{ $activeAssignment->vehicle?->plate_number }}
                            </a>
                        </h5>
                        <p class="mb-0 text-muted">
                            {{ $activeAssignment->vehicle?->make }}
                            {{ $activeAssignment->vehicle?->model }}
                        </p>
                        <small class="text-muted">
                            تاريخ التعيين: {{ $activeAssignment->assigned_at?->format('Y-m-d') }}
                        </small>
                    </div>
                </div>
            @else
                <p class="text-muted text-center py-3">
                    <i class="la la-car font-large-1"></i><br>
                    لا توجد مركبة معينة حالياً
                </p>
            @endif

        </div>
    </div>
</div>
