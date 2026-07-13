@extends('layouts.dashboard.app')

@section('title') تسويات هنقرستيشن — {{ $period->getDisplayLabel() }} @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-10 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.monthly.periods.index') }}">الفترات الشهرية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.monthly.periods.show', $period) }}">{{ $period->getDisplayLabel() }}</a></li>
                            <li class="breadcrumb-item active">تسويات هنقرستيشن</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">

            @foreach(['success','error','info','warning'] as $flash)
                @if(session($flash))
                    <div class="alert alert-{{ $flash === 'error' ? 'danger' : $flash }} alert-dismissible fade show">
                        {{ session($flash) }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif
            @endforeach

            @include('dashboard.monthly.hungerstation_ftr._pending_entries_banner')

            {{-- Batch info --}}
            @if ($activeBatch)
                <div class="card mb-2" style="border-right:4px solid #4e73df;">
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <small class="text-muted d-block">الملف المستورد</small>
                                <strong>{{ $activeBatch->original_filename }}</strong>
                            </div>
                            <div class="col-md-2 text-center">
                                <small class="text-muted d-block">الركاب</small>
                                <strong>{{ $activeBatch->total_riders }}</strong>
                            </div>
                            <div class="col-md-2 text-center">
                                <small class="text-muted d-block">المطابقون</small>
                                <strong>{{ $activeBatch->matched_delegates }}</strong>
                            </div>
                            <div class="col-md-2 text-center">
                                <small class="text-muted d-block">تاريخ الاستيراد</small>
                                <strong>{{ $activeBatch->imported_at?->format('Y-m-d') ?? '—' }}</strong>
                            </div>
                            <div class="col-md-3 text-left">
                                <a href="{{ route('dashboard.monthly.periods.hungerstation.ftr.import', $period) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="la la-upload"></i> إعادة الاستيراد
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="la la-exclamation-triangle mr-1"></i>
                    لا يوجد استيراد لهذه الفترة.
                    <a href="{{ route('dashboard.monthly.periods.hungerstation.ftr.import', $period) }}" class="alert-link">رفع ملف الفاتورة</a>
                </div>
            @endif

            {{-- Financial totals --}}
            <div class="row mb-2">
                <div class="col-md-2 col-sm-4 mb-1">
                    <div class="card text-center" style="border-right:4px solid #4e73df;">
                        <div class="card-body py-2">
                            <div class="font-medium-3 font-weight-bold">{{ number_format($totals['basic_payment'], 2) }}</div>
                            <small class="text-muted">الدفع الأساسي</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 mb-1">
                    <div class="card text-center" style="border-right:4px solid #1cc88a;">
                        <div class="card-body py-2">
                            <div class="font-medium-3 font-weight-bold">{{ number_format($totals['distance_payment'], 2) }}</div>
                            <small class="text-muted">مدفوعات المسافات</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 mb-1">
                    <div class="card text-center" style="border-right:4px solid #e74a3b;">
                        <div class="card-body py-2">
                            <div class="font-medium-3 font-weight-bold text-danger">
                                ({{ number_format($totals['total_platform_penalties'], 2) }})
                            </div>
                            <small class="text-muted">غرامات المنصة</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 mb-1">
                    <div class="card text-center" style="border-right:4px solid #f6c23e;">
                        <div class="card-body py-2">
                            <div class="font-medium-3 font-weight-bold text-info">
                                ({{ number_format($totals['stacking_deduction'], 2) }})
                            </div>
                            <small class="text-muted">خصم الطلبات المتعددة</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 mb-1">
                    <div class="card text-center" style="border-right:4px solid #858796;">
                        <div class="card-body py-2">
                            <div class="font-medium-3 font-weight-bold text-warning">
                                ({{ number_format($totals['rider_balance'], 2) }})
                            </div>
                            <small class="text-muted">رصيد المحفظة</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 mb-1">
                    <div class="card text-center" style="border-right:4px solid #20c997;">
                        <div class="card-body py-2">
                            <div class="font-medium-3 font-weight-bold">{{ number_format($totals['net_salary'], 2) }}</div>
                            <small class="text-muted">صافي الرواتب</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Settlements table --}}
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        تسويات المندوبين ({{ $settlements->count() }})
                    </h4>
                </div>
                <div class="card-body p-0">
                    @if ($settlements->isEmpty())
                        <div class="p-2 text-muted">لا توجد تسويات لهذه الفترة.</div>
                    @else
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>المندوب</th>
                                    <th class="text-center">المعرف</th>
                                    <th class="text-center">الطلبات</th>
                                    <th class="text-center">أيام العمل</th>
                                    <th class="text-right">صافي الراتب</th>
                                    <th class="text-center">تسويات</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($settlements as $s)
                                    <tr>
                                        <td>
                                            @if ($s->is_locked)
                                                <i class="la la-lock text-secondary" title="مقفل"></i>
                                            @endif
                                            {{ $s->delegate?->name ?? '—' }}
                                            @if ($s->total_platform_penalties > 0)
                                                <br><small class="text-danger">غرامات: ({{ number_format($s->total_platform_penalties, 2) }})</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <code class="small">{{ $s->rider_id_platform }}</code>
                                        </td>
                                        <td class="text-center">
                                            {{ number_format($s->total_orders) }}
                                        </td>
                                        <td class="text-center">
                                            @if ($s->working_days !== null)
                                                {{ $s->working_days }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold {{ $s->net_salary < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($s->net_salary, 2) }}
                                        </td>
                                        <td class="text-center">
                                            @if ($s->deductions_count > 0)
                                                <span class="badge badge-warning">{{ $s->deductions_count }}</span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('dashboard.monthly.periods.hungerstation.ftr.settlement.show', [$period, $s]) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="la la-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
