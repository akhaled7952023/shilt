<div class="card">
    <div class="card-header">
        <h4 class="card-title">سجل النشاط</h4>
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
                $auditService = app(\App\Services\AuditService::class);
                $timeline     = $auditService->getTimelineForDelegate($delegate->id, 30);
            @endphp

            @forelse ($timeline as $entry)
                <div class="d-flex align-items-start mb-1 pb-1" style="border-bottom: 1px solid #f0f0f0;">
                    <div class="mr-2 mt-1" style="min-width: 28px;">
                        @switch($entry->action)
                            @case('created')
                                <i class="la la-plus-circle text-success font-medium-3"></i>
                                @break
                            @case('updated')
                                <i class="la la-edit text-info font-medium-3"></i>
                                @break
                            @case('deleted')
                                <i class="la la-trash text-danger font-medium-3"></i>
                                @break
                            @case('status_changed')
                                <i class="la la-refresh text-warning font-medium-3"></i>
                                @break
                            @case('deactivated')
                                <i class="la la-ban text-warning font-medium-3"></i>
                                @break
                            @case('assigned')
                                <i class="la la-car text-primary font-medium-3"></i>
                                @break
                            @case('returned')
                                <i class="la la-undo text-secondary font-medium-3"></i>
                                @break
                            @default
                                <i class="la la-info-circle text-muted font-medium-3"></i>
                        @endswitch
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-0" style="font-size:0.9rem;">
                            @switch($entry->action)
                                @case('created')
                                    @switch($entry->model_type)
                                        @case('App\Models\Delegate') تم تسجيل المندوب @break
                                        @case('App\Models\DelegateDocument') تم رفع وثيقة @break
                                        @case('App\Models\DelegatePlatformAssignment') تم تعيين المندوب على منصة @break
                                        @case('App\Models\VehicleAssignment') تم تعيين مركبة للمندوب @break
                                        @default تم الإنشاء
                                    @endswitch
                                    @break
                                @case('updated')
                                    تم تحديث البيانات
                                    @break
                                @case('status_changed')
                                    تم تغيير الحالة
                                    @if (!empty($entry->old_values['status']) && !empty($entry->new_values['status']))
                                        من
                                        <span class="badge badge-secondary">{{ $entry->old_values['status'] }}</span>
                                        إلى
                                        <span class="badge badge-primary">{{ $entry->new_values['status'] }}</span>
                                    @endif
                                    @break
                                @case('deactivated')
                                    تم إلغاء تفعيل تعيين المنصة
                                    @break
                                @case('assigned')
                                    تم تعيين مركبة
                                    @break
                                @case('returned')
                                    تم إرجاع المركبة
                                    @break
                                @case('deleted')
                                    تم حذف سجل
                                    @break
                                @default
                                    {{ $entry->action }}
                            @endswitch
                        </p>
                        <small class="text-muted">
                            {{ $entry->user?->name ?? 'النظام' }}
                            &nbsp;·&nbsp;
                            {{ $entry->created_at?->diffForHumans() ?? '—' }}
                        </small>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-3">لا توجد أحداث مسجلة</div>
            @endforelse

        </div>
    </div>
</div>
