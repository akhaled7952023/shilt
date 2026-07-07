@extends('layouts.dashboard.app')

@section('title') تسوية {{ $settlement->delegate?->name ?? '—' }} — {{ $period->getDisplayLabel() }} @endsection

@php
use App\Models\HungerStationFtrDelegateDeduction;

$benefits   = $settlement->deductions->where('is_benefit', true);
$deductions = $settlement->deductions->where('is_benefit', false);

$penaltyMap = [
    'acceptance_rate_penalties'       => 'نسبة قبول الطلبات',
    'contact_rate_penalties'          => 'نسبة التواصل',
    'declined_penalties'              => 'غرامات الرفض',
    'late_penalty'                    => 'غرامات التأخير',
    'no_show_penalty'                 => 'عدم الحضور',
    'no_show_penalty_special_cities'  => 'عدم الحضور (مدن خاصة)',
    'daily_acceptance_rate_penalty'   => 'غرامة نسبة القبول اليومية',
    'missed_days_penalty'             => 'أيام مفقودة',
];

$adjustRoute    = route('dashboard.monthly.periods.hungerstation.ftr.settlement.adjustments.store', [$period, $settlement]);
$destroyBaseUrl = url("dashboard/monthly/periods/{$period->id}/hungerstation/ftr/settlement/{$settlement->id}/adjustments") . '/';
$csrfToken      = csrf_token();
@endphp

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
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.monthly.periods.hungerstation.ftr.settlement.index', $period) }}">تسويات هنقرستيشن</a></li>
                            <li class="breadcrumb-item active">{{ $settlement->delegate?->name ?? '—' }}</li>
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

            @if($isReadOnly)
                <div class="alert alert-secondary mb-2">
                    <i class="la la-lock mr-1"></i>
                    @if($settlement->is_locked)
                        <strong>هذه التسوية مقفلة</strong> — لا يمكن إجراء أي تعديلات.
                    @else
                        <strong>الفترة مغلقة</strong> — لا يمكن إجراء أي تعديلات.
                    @endif
                </div>
            @endif

            {{-- شريط بيانات المندوب --}}
            <div class="card mb-2" style="border-right: 4px solid #1cc88a;">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <small class="text-muted d-block">المندوب</small>
                            <strong class="font-medium-2">{{ $settlement->delegate?->name ?? '—' }}</strong>
                            <br><small class="text-muted">{{ $settlement->delegate?->delegate_code ?? '' }}</small>
                        </div>
                        <div class="col-md-2 text-center">
                            <small class="text-muted d-block">المعرف</small>
                            <code class="font-medium-2">{{ $settlement->rider_id_platform }}</code>
                        </div>
                        <div class="col-md-2 text-center">
                            <small class="text-muted d-block">مدفوعات المسافات</small>
                            <strong class="font-medium-2">{{ number_format($settlement->distance_payment, 2) }}</strong>
                        </div>
                        <div class="col-md-2 text-center">
                            <small class="text-muted d-block">غرامات المنصة</small>
                            <strong class="font-medium-2 text-danger">
                                @if ($settlement->total_platform_penalties > 0)
                                    ({{ number_format($settlement->total_platform_penalties, 2) }})
                                @else —
                                @endif
                            </strong>
                        </div>
                        <div class="col-md-3 text-left">
                            <small class="text-muted d-block">المبلغ المستحق</small>
                            <strong id="header-net-salary"
                                    class="font-large-1 {{ $settlement->net_salary < 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($settlement->net_salary, 2) }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- التبويبات --}}
            <ul class="nav nav-tabs mb-0" id="settlementTabs">
                @php
                    $tabs = [
                        'imported'    => ['icon' => 'la-download',    'label' => 'بيانات الاستيراد'],
                        'calculation' => ['icon' => 'la-calculator',  'label' => 'الحساب والغرامات'],
                        'adjustments' => ['icon' => 'la-sliders',     'label' => 'تسويات الشركة'],
                        'summary'     => ['icon' => 'la-list-alt',    'label' => 'الملخص'],
                        'review'      => ['icon' => 'la-eye',         'label' => 'المراجعة'],
                        'result'      => ['icon' => 'la-check-circle','label' => 'المبلغ المستحق'],
                    ];
                @endphp
                @foreach ($tabs as $key => $tab)
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === $key ? 'active' : '' }}"
                           href="{{ route('dashboard.monthly.periods.hungerstation.ftr.settlement.show', [$period, $settlement, 'tab' => $key]) }}">
                            <i class="la {{ $tab['icon'] }}"></i> {{ $tab['label'] }}
                            @if ($key === 'adjustments' && $settlement->deductions->count())
                                <span class="badge badge-warning ml-1">{{ $settlement->deductions->count() }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="card" style="border-top-left-radius:0; border-top-right-radius:0;">
                <div class="card-body">

                    {{-- ════════ التبويب 1: بيانات الاستيراد ════════ --}}
                    @if ($activeTab === 'imported')
                        <h5 class="mb-3"><i class="la la-database text-primary"></i> بيانات الاستيراد من المنصة (قراءة فقط)</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted border-bottom pb-1 mb-2">الأرباح والمدفوعات</h6>
                                <table class="table table-sm table-bordered">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted">الدفع الأساسي</td>
                                            <td class="font-weight-bold text-primary">{{ number_format($settlement->basic_payment, 2) }} ريال</td>
                                            <td class="text-muted small">إيرادات الشركة من المنصة</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">مدفوعات المسافات</td>
                                            <td class="font-weight-bold text-success">{{ number_format($settlement->distance_payment, 2) }} ريال</td>
                                            <td class="text-muted small">أساس راتب المندوب</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">إجمالي الطلبات</td>
                                            <td class="font-weight-bold">{{ number_format($settlement->total_orders) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">رصيد المحفظة</td>
                                            <td class="{{ $settlement->rider_balance > 0 ? 'text-warning font-weight-bold' : '' }}">
                                                {{ $settlement->rider_balance > 0 ? number_format($settlement->rider_balance, 2) . ' ريال' : '—' }}
                                            </td>
                                            <td class="text-muted small">مُستلمة عبر المحفظة مسبقاً</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted border-bottom pb-1 mb-2">غرامات المنصة (مستوردة)</h6>
                                <table class="table table-sm table-bordered">
                                    <tbody>
                                        @foreach ($penaltyMap as $field => $label)
                                            @php $val = (float)($settlement->$field ?? 0); @endphp
                                            <tr>
                                                <td class="text-muted small">{{ $label }}</td>
                                                <td class="{{ $val > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                                    {{ $val > 0 ? '('.number_format($val, 2).')' : '—' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-warning">
                                            <td class="text-muted small">
                                                خصم الطلبات المتعددة (تتحمله الشركة)
                                                <br><small class="text-info">لا يُخصم من راتب المندوب</small>
                                            </td>
                                            <td class="text-info">
                                                {{ $settlement->stacking_deduction > 0 ? number_format($settlement->stacking_deduction, 2) : '—' }}
                                            </td>
                                        </tr>
                                        <tr class="table-light">
                                            <td class="font-weight-bold">إجمالي الغرامات المخصومة</td>
                                            <td class="font-weight-bold text-danger">
                                                ({{ number_format($settlement->total_platform_penalties, 2) }})
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- ════════ التبويب 2: الحساب والغرامات ════════ --}}
                    @if ($activeTab === 'calculation')
                        <h5 class="mb-3"><i class="la la-calculator text-warning"></i> تفصيل الحساب والغرامات</h5>

                        <div class="row">
                            <div class="col-md-7">
                                <h6 class="text-muted border-bottom pb-1 mb-2">غرامات المنصة</h6>
                                <table class="table table-sm table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr><th>#</th><th>نوع الغرامة</th><th class="text-right">المبلغ</th><th class="text-right">التأثير</th></tr>
                                    </thead>
                                    <tbody>
                                        @php $n = 1; @endphp
                                        @foreach ($penaltyMap as $field => $label)
                                            @php $val = (float)($settlement->$field ?? 0); @endphp
                                            <tr class="{{ $val > 0 ? '' : 'text-muted' }}">
                                                <td>{{ $n++ }}</td>
                                                <td>{{ $label }}</td>
                                                <td class="text-right {{ $val > 0 ? 'text-danger' : '' }}">
                                                    {{ $val > 0 ? number_format($val, 2) : '—' }}
                                                </td>
                                                <td class="text-right small {{ $val > 0 ? 'text-danger' : 'text-muted' }}">
                                                    {{ $val > 0 ? 'تُخصم من الراتب' : '' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-warning">
                                            <td>—</td>
                                            <td><strong>خصم الطلبات المتعددة (تتحمله الشركة)</strong></td>
                                            <td class="text-right text-info">
                                                {{ $settlement->stacking_deduction > 0 ? number_format($settlement->stacking_deduction, 2) : '—' }}
                                            </td>
                                            <td class="text-right small text-info">
                                                {{ $settlement->stacking_deduction > 0 ? 'تتحملها الشركة' : '' }}
                                            </td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td colspan="2"><strong>إجمالي الغرامات المخصومة من الراتب</strong></td>
                                            <td class="text-right font-weight-bold text-danger">
                                                ({{ number_format($settlement->total_platform_penalties, 2) }})
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-5">
                                <h6 class="text-muted border-bottom pb-1 mb-2">معادلة الحساب</h6>
                                <div class="card bg-light">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>مدفوعات المسافات</span>
                                            <strong class="text-success">+ {{ number_format($settlement->distance_payment, 2) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>غرامات المنصة</span>
                                            <strong class="text-danger">− {{ number_format($settlement->total_platform_penalties, 2) }}</strong>
                                        </div>
                                        @if ($settlement->rider_balance > 0)
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>رصيد المحفظة</span>
                                            <strong class="text-warning">− {{ number_format(abs($settlement->rider_balance), 2) }}</strong>
                                        </div>
                                        @endif
                                        @if ($settlement->company_benefits_total > 0)
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>مزايا الشركة</span>
                                            <strong class="text-success">+ {{ number_format($settlement->company_benefits_total, 2) }}</strong>
                                        </div>
                                        @endif
                                        @if ($settlement->company_deductions_total > 0)
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>خصومات الشركة</span>
                                            <strong class="text-danger">− {{ number_format($settlement->company_deductions_total, 2) }}</strong>
                                        </div>
                                        @endif
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between">
                                            <strong>المبلغ المستحق</strong>
                                            <strong class="font-medium-2 {{ $settlement->net_salary < 0 ? 'text-danger' : 'text-success' }}">
                                                = {{ number_format($settlement->net_salary, 2) }} ريال
                                            </strong>
                                        </div>
                                        @if ($settlement->stacking_deduction > 0)
                                        <div class="mt-2 p-2 rounded" style="background:#e8f4fd;">
                                            <small class="text-info">
                                                <i class="la la-info-circle"></i>
                                                خصم الطلبات المتعددة: {{ number_format($settlement->stacking_deduction, 2) }} ريال — تتحملها الشركة ولا تُخصم من راتب المندوب
                                            </small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ════════ التبويب 3: تسويات الشركة ════════ --}}
                    @if ($activeTab === 'adjustments')
                        <h5 class="mb-3">
                            <i class="la la-sliders text-primary"></i>
                            تسويات الشركة
                        </h5>

                        {{-- نموذج إضافة تسوية --}}
                        @if (!$isReadOnly)
                        <div class="card mb-3 border-primary">
                            <div class="card-header py-2" style="background:#f0f4ff;">
                                <strong><i class="la la-plus-circle text-primary"></i> إضافة تسوية جديدة</strong>
                            </div>
                            <div class="card-body py-3">
                                <form method="POST" id="add-adjustment-form" action="{{ $adjustRoute }}">
                                    @csrf
                                    {{-- اختيار الاتجاه --}}
                                    <div class="mb-3">
                                        <label class="d-block font-weight-bold mb-1">نوع التسوية:</label>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-danger adj-dir-btn" data-value="0"
                                                    onclick="setDirection(0)">
                                                <i class="la la-minus-circle"></i> خصم
                                            </button>
                                            <button type="button" class="btn btn-outline-success adj-dir-btn" data-value="1"
                                                    onclick="setDirection(1)">
                                                <i class="la la-plus-circle"></i> مزية
                                            </button>
                                        </div>
                                        <input type="hidden" name="is_benefit" id="adj-is-benefit" value="0">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">الصنف</label>
                                                <select name="deduction_type" id="adj-type-select"
                                                        class="form-control form-control-sm" required>
                                                    <optgroup id="adj-deduction-opts" label="— خصومات">
                                                        @foreach (HungerStationFtrDelegateDeduction::DEDUCTION_TYPE_LABELS as $k => $v)
                                                            <option value="{{ $k }}">{{ $v }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                    <optgroup id="adj-benefit-opts" label="— مزايا" style="display:none;">
                                                        @foreach (HungerStationFtrDelegateDeduction::BENEFIT_TYPE_LABELS as $k => $v)
                                                            <option value="{{ $k }}">{{ $v }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">المبلغ (ريال)</label>
                                                <input type="number" name="amount" id="adj-amount"
                                                       class="form-control form-control-sm"
                                                       step="0.01" min="0.01" required placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">ملاحظة <small class="text-muted">(اختياري)</small></label>
                                                <input type="text" name="notes" id="adj-notes"
                                                       class="form-control form-control-sm" placeholder="سبب التسوية">
                                            </div>
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="submit" class="btn btn-sm btn-primary mb-2" id="add-adj-btn">
                                                <i class="la la-save"></i> إضافة
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif

                        {{-- جدول المزايا --}}
                        <div class="card mb-3" id="benefits-section">
                            <div class="card-header py-2 d-flex justify-content-between align-items-center"
                                 style="background:#d4edda;">
                                <strong><i class="la la-plus-circle text-success"></i> مزايا الشركة</strong>
                                <span class="font-weight-bold text-success" id="benefits-total-display">
                                    + {{ number_format($settlement->company_benefits_total, 2) }} ريال
                                </span>
                            </div>
                            <div class="card-body p-0" id="benefits-table-wrap">
                                @if ($benefits->isEmpty())
                                    <p class="text-muted text-center py-3 mb-0">لا توجد مزايا مضافة.</p>
                                @else
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>الصنف</th>
                                                <th class="text-right">المبلغ</th>
                                                <th>ملاحظة</th>
                                                @if (!$isReadOnly) <th style="width:80px;"></th> @endif
                                            </tr>
                                        </thead>
                                        <tbody id="benefits-tbody">
                                            @foreach ($benefits as $b)
                                                <tr id="adj-row-{{ $b->id }}">
                                                    <td>{{ HungerStationFtrDelegateDeduction::BENEFIT_TYPE_LABELS[$b->deduction_type] ?? $b->label }}</td>
                                                    <td class="text-right text-success font-weight-bold">+ {{ number_format($b->amount, 2) }}</td>
                                                    <td class="text-muted small">{{ $b->notes ?? '—' }}</td>
                                                    @if (!$isReadOnly)
                                                        <td>
                                                            <button type="button" class="btn btn-xs btn-outline-primary edit-adj-btn mr-1"
                                                                    data-id="{{ $b->id }}"
                                                                    data-is-benefit="1"
                                                                    data-type="{{ $b->deduction_type }}"
                                                                    data-label="{{ $b->label }}"
                                                                    data-amount="{{ $b->amount }}"
                                                                    data-notes="{{ $b->notes ?? '' }}"
                                                                    title="تعديل"><i class="la la-edit"></i></button>
                                                            <button type="button" class="btn btn-xs btn-outline-danger delete-adj-btn"
                                                                    data-id="{{ $b->id }}"
                                                                    title="حذف"><i class="la la-trash"></i></button>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>

                        {{-- جدول الخصومات --}}
                        <div class="card mb-3" id="deductions-section">
                            <div class="card-header py-2 d-flex justify-content-between align-items-center"
                                 style="background:#f8d7da;">
                                <strong><i class="la la-minus-circle text-danger"></i> خصومات الشركة</strong>
                                <span class="font-weight-bold text-danger" id="deductions-total-display">
                                    − {{ number_format($settlement->company_deductions_total, 2) }} ريال
                                </span>
                            </div>
                            <div class="card-body p-0" id="deductions-table-wrap">
                                @if ($deductions->isEmpty())
                                    <p class="text-muted text-center py-3 mb-0">لا توجد خصومات مضافة.</p>
                                @else
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>الصنف</th>
                                                <th class="text-right">المبلغ</th>
                                                <th>ملاحظة</th>
                                                @if (!$isReadOnly) <th style="width:80px;"></th> @endif
                                            </tr>
                                        </thead>
                                        <tbody id="deductions-tbody">
                                            @foreach ($deductions as $d)
                                                <tr id="adj-row-{{ $d->id }}">
                                                    <td>{{ HungerStationFtrDelegateDeduction::DEDUCTION_TYPE_LABELS[$d->deduction_type] ?? HungerStationFtrDelegateDeduction::TYPE_LABELS[$d->deduction_type] ?? $d->label }}</td>
                                                    <td class="text-right text-danger font-weight-bold">− {{ number_format($d->amount, 2) }}</td>
                                                    <td class="text-muted small">{{ $d->notes ?? '—' }}</td>
                                                    @if (!$isReadOnly)
                                                        <td>
                                                            <button type="button" class="btn btn-xs btn-outline-primary edit-adj-btn mr-1"
                                                                    data-id="{{ $d->id }}"
                                                                    data-is-benefit="0"
                                                                    data-type="{{ $d->deduction_type }}"
                                                                    data-label="{{ $d->label }}"
                                                                    data-amount="{{ $d->amount }}"
                                                                    data-notes="{{ $d->notes ?? '' }}"
                                                                    title="تعديل"><i class="la la-edit"></i></button>
                                                            <button type="button" class="btn btn-xs btn-outline-danger delete-adj-btn"
                                                                    data-id="{{ $d->id }}"
                                                                    title="حذف"><i class="la la-trash"></i></button>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>

                        {{-- معاينة صافي الراتب اللحظية --}}
                        <div class="p-3 rounded d-flex justify-content-between align-items-center"
                             style="background:#f0fff4; border:1px solid #1cc88a;">
                            <div>
                                <span class="font-weight-bold">المبلغ المستحق الحالي:</span>
                                <br><small class="text-muted">
                                    {{ number_format($settlement->distance_payment,2) }}
                                    − {{ number_format($settlement->total_platform_penalties,2) }}
                                    − {{ number_format(abs($settlement->rider_balance),2) }}
                                    + <span id="preview-benefits">{{ number_format($settlement->company_benefits_total,2) }}</span>
                                    − <span id="preview-deductions">{{ number_format($settlement->company_deductions_total,2) }}</span>
                                </small>
                            </div>
                            <span id="live-net-salary"
                                  class="font-large-1 font-weight-bold {{ $settlement->net_salary < 0 ? 'text-danger' : 'text-success' }}"
                                  data-base="{{ $settlement->distance_payment }}"
                                  data-penalties="{{ $settlement->total_platform_penalties }}"
                                  data-wallet="{{ abs($settlement->rider_balance) }}"
                                  data-benefits="{{ $settlement->company_benefits_total }}"
                                  data-deductions="{{ $settlement->company_deductions_total }}">
                                {{ number_format($settlement->net_salary, 2) }} ريال
                            </span>
                        </div>

                        {{-- نافذة التعديل --}}
                        @if (!$isReadOnly)
                        <div class="modal fade" id="editAdjModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header py-2">
                                        <h6 class="modal-title"><i class="la la-edit"></i> تعديل التسوية</h6>
                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                    </div>
                                    <form id="edit-adj-form" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label class="small font-weight-bold">الصنف</label>
                                                <select name="deduction_type" id="edit-adj-type"
                                                        class="form-control form-control-sm">
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="small font-weight-bold">المبلغ (ريال)</label>
                                                <input type="number" name="amount" id="edit-adj-amount"
                                                       class="form-control form-control-sm" step="0.01" min="0.01" required>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="small font-weight-bold">ملاحظة <small class="text-muted">(اختياري)</small></label>
                                                <input type="text" name="notes" id="edit-adj-notes"
                                                       class="form-control form-control-sm">
                                            </div>
                                        </div>
                                        <div class="modal-footer py-2">
                                            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">إلغاء</button>
                                            <button type="submit" class="btn btn-sm btn-primary" id="edit-adj-submit">
                                                <i class="la la-save"></i> حفظ التعديل
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif

                    {{-- ════════ التبويب 4: الملخص ════════ --}}
                    @if ($activeTab === 'summary')
                        <h5 class="mb-3"><i class="la la-list-alt text-info"></i> ملخص التسوية الشاملة</h5>

                        <div class="row">
                            {{-- القيم المستوردة --}}
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-header bg-primary text-white py-2">
                                        <strong><i class="la la-download"></i> القيم المستوردة</strong>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0">
                                            <tr>
                                                <td class="text-muted">إجمالي الطلبات</td>
                                                <td class="text-right font-weight-bold">{{ number_format($settlement->total_orders) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">مدفوعات المسافات</td>
                                                <td class="text-right font-weight-bold text-success">+ {{ number_format($settlement->distance_payment, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">الدفع الأساسي <small class="text-muted">(محاسبي)</small></td>
                                                <td class="text-right text-primary">{{ number_format($settlement->basic_payment, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">رصيد المحفظة</td>
                                                <td class="text-right {{ $settlement->rider_balance > 0 ? 'text-warning font-weight-bold' : '' }}">
                                                    @if ($settlement->rider_balance > 0)
                                                        − {{ number_format(abs($settlement->rider_balance), 2) }}
                                                    @else —
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr class="table-danger">
                                                <td class="font-weight-bold">غرامات المنصة</td>
                                                <td class="text-right font-weight-bold text-danger">− {{ number_format($settlement->total_platform_penalties, 2) }}</td>
                                            </tr>
                                            @if ($settlement->stacking_deduction > 0)
                                                <tr class="table-warning">
                                                    <td class="small">خصم الطلبات المتعددة <small class="text-info">(تتحمله الشركة)</small></td>
                                                    <td class="text-right text-info">{{ number_format($settlement->stacking_deduction, 2) }} <small>(لا يُخصم)</small></td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- تسويات الشركة --}}
                            <div class="col-md-6 mb-3">
                                <div class="card mb-3">
                                    <div class="card-header py-2" style="background:#d4edda;">
                                        <strong><i class="la la-plus-circle text-success"></i> مزايا الشركة</strong>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0">
                                            @forelse ($benefits as $b)
                                                <tr>
                                                    <td class="text-muted small">{{ $b->label }}</td>
                                                    <td class="text-right text-success">+ {{ number_format($b->amount, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2" class="text-muted text-center small">لا توجد مزايا</td></tr>
                                            @endforelse
                                            @if ($benefits->isNotEmpty())
                                                <tr class="table-success">
                                                    <td class="font-weight-bold small">الإجمالي</td>
                                                    <td class="text-right font-weight-bold text-success">+ {{ number_format($settlement->company_benefits_total, 2) }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header py-2" style="background:#f8d7da;">
                                        <strong><i class="la la-minus-circle text-danger"></i> خصومات الشركة</strong>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0">
                                            @forelse ($deductions as $d)
                                                <tr>
                                                    <td class="text-muted small">{{ $d->label }}</td>
                                                    <td class="text-right text-danger">− {{ number_format($d->amount, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2" class="text-muted text-center small">لا توجد خصومات</td></tr>
                                            @endforelse
                                            @if ($deductions->isNotEmpty())
                                                <tr class="table-danger">
                                                    <td class="font-weight-bold small">الإجمالي</td>
                                                    <td class="text-right font-weight-bold text-danger">− {{ number_format($settlement->company_deductions_total, 2) }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- المعادلة النهائية --}}
                        <div class="card" style="border: 2px solid #1cc88a;">
                            <div class="card-header py-2" style="background:#1cc88a; color:#fff;">
                                <strong><i class="la la-equals"></i> المعادلة النهائية</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                                            <span>مدفوعات المسافات</span>
                                            <strong class="text-success">+ {{ number_format($settlement->distance_payment, 2) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                                            <span>غرامات المنصة</span>
                                            <strong class="text-danger">{{ number_format($settlement->total_platform_penalties, 2) }}</strong>
                                        </div>
                                        @if ($settlement->rider_balance > 0)
                                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                                            <span>رصيد المحفظة</span>
                                            <strong class="text-warning">{{ number_format(abs($settlement->rider_balance), 2) }}</strong>
                                        </div>
                                        @endif
                                        @if ($settlement->company_benefits_total > 0)
                                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                                            <span>مزايا الشركة</span>
                                            <strong class="text-success">{{ number_format($settlement->company_benefits_total, 2) }}</strong>
                                        </div>
                                        @endif
                                        @if ($settlement->company_deductions_total > 0)
                                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                                            <span>خصومات الشركة</span>
                                            <strong class="text-danger">{{ number_format($settlement->company_deductions_total, 2) }}</strong>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="col-md-6 d-flex align-items-center justify-content-center">
                                        <div class="text-center">
                                            <div class="text-muted mb-1">المبلغ المستحق</div>
                                            <div class="font-large-2 font-weight-bold {{ $settlement->net_salary < 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($settlement->net_salary, 2) }} ريال
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ════════ التبويب 5: المراجعة ════════ --}}
                    @if ($activeTab === 'review')
                        <h5 class="mb-3"><i class="la la-eye text-secondary"></i> المراجعة النهائية (قراءة فقط)</h5>

                        <div class="row">
                            <div class="col-md-4">
                                <table class="table table-sm table-bordered">
                                    <thead><tr class="table-primary"><th colspan="2">بيانات المندوب</th></tr></thead>
                                    <tbody>
                                        <tr><td class="text-muted">الاسم</td><td>{{ $settlement->delegate?->name ?? '—' }}</td></tr>
                                        <tr><td class="text-muted">كود المندوب</td><td>{{ $settlement->delegate?->delegate_code ?? '—' }}</td></tr>
                                        <tr><td class="text-muted">المعرف</td><td><code>{{ $settlement->rider_id_platform }}</code></td></tr>
                                        <tr><td class="text-muted">الفترة</td><td>{{ $period->getDisplayLabel() }}</td></tr>
                                        <tr><td class="text-muted">إجمالي الطلبات</td><td>{{ number_format($settlement->total_orders) }}</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-4">
                                <table class="table table-sm table-bordered">
                                    <thead><tr class="table-info"><th colspan="2">القيم المستوردة</th></tr></thead>
                                    <tbody>
                                        <tr><td class="text-muted">الدفع الأساسي</td><td>{{ number_format($settlement->basic_payment, 2) }}</td></tr>
                                        <tr><td class="text-muted">مدفوعات المسافات</td><td class="text-success font-weight-bold">{{ number_format($settlement->distance_payment, 2) }}</td></tr>
                                        <tr><td class="text-muted">غرامات المنصة</td><td class="text-danger">({{ number_format($settlement->total_platform_penalties, 2) }})</td></tr>
                                        <tr><td class="text-muted">خصم متعدد <small class="text-secondary">(تتحمله الشركة)</small></td><td class="text-info">{{ number_format($settlement->stacking_deduction, 2) }}</td></tr>
                                        <tr><td class="text-muted">رصيد المحفظة</td><td class="text-warning">{{ number_format($settlement->rider_balance, 2) }}</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-4">
                                <table class="table table-sm table-bordered">
                                    <thead><tr class="table-success"><th colspan="2">مزايا الشركة</th></tr></thead>
                                    <tbody>
                                        @forelse ($benefits as $b)
                                            <tr>
                                                <td class="text-muted small">{{ $b->label }}</td>
                                                <td class="text-success">+ {{ number_format($b->amount, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-muted text-center small">لا توجد مزايا</td></tr>
                                        @endforelse
                                        @if ($benefits->isNotEmpty())
                                            <tr class="table-success"><td class="font-weight-bold small">الإجمالي</td><td class="text-success font-weight-bold">+ {{ number_format($settlement->company_benefits_total, 2) }}</td></tr>
                                        @endif
                                    </tbody>
                                </table>
                                <table class="table table-sm table-bordered mt-2">
                                    <thead><tr class="table-danger"><th colspan="2">خصومات الشركة</th></tr></thead>
                                    <tbody>
                                        @forelse ($deductions as $d)
                                            <tr>
                                                <td class="text-muted small">{{ $d->label }}</td>
                                                <td class="text-danger">({{ number_format($d->amount, 2) }})</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-muted text-center small">لا توجد خصومات</td></tr>
                                        @endforelse
                                        @if ($deductions->isNotEmpty())
                                            <tr class="table-danger"><td class="font-weight-bold small">الإجمالي</td><td class="text-danger font-weight-bold">({{ number_format($settlement->company_deductions_total, 2) }})</td></tr>
                                        @endif
                                    </tbody>
                                </table>
                                <div class="alert alert-success text-center py-2 mb-0 mt-2">
                                    <strong>المبلغ المستحق</strong><br>
                                    <span class="font-large-1 font-weight-bold">{{ number_format($settlement->net_salary, 2) }} ريال</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ════════ التبويب 6: المبلغ المستحق ════════ --}}
                    @if ($activeTab === 'result')
                        <div class="text-center py-5">
                            <div class="d-inline-block p-5 rounded-lg"
                                 style="background:linear-gradient(135deg,#1cc88a,#17a673); box-shadow:0 20px 60px rgba(28,200,138,.3); min-width:400px;">
                                <div class="text-white mb-3">
                                    <div style="font-size:1rem; opacity:.85;">المندوب</div>
                                    <div style="font-size:1.3rem; font-weight:700;">{{ $settlement->delegate?->name ?? '—' }}</div>
                                    <div style="font-size:.85rem; opacity:.7;">{{ $settlement->delegate?->delegate_code ?? '' }}</div>
                                </div>
                                <div class="text-white mb-3" style="opacity:.8; font-size:.9rem;">{{ $period->getDisplayLabel() }}</div>
                                <div class="text-white mb-2" style="font-size:1rem; opacity:.85;">المبلغ المستحق للصرف</div>
                                <div class="text-white" style="font-size:3.5rem; font-weight:900; line-height:1.1;">
                                    {{ number_format($settlement->net_salary, 2) }}
                                </div>
                                <div class="text-white mt-1" style="font-size:1.2rem; opacity:.8;">ريال سعودي</div>

                                @if ($settlement->net_salary < 0)
                                    <div class="mt-3 p-2 rounded" style="background:rgba(255,255,255,.2);">
                                        <small class="text-white"><i class="la la-exclamation-triangle"></i>
                                            المبلغ سالب — يرجى مراجعة الخصومات
                                        </small>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 text-muted small">
                                <strong>تفصيل الحساب:</strong>
                                {{ number_format($settlement->distance_payment, 2) }} (مدفوعات المسافات)
                                − {{ number_format($settlement->total_platform_penalties, 2) }} (غرامات المنصة)
                                @if ($settlement->rider_balance != 0) − {{ number_format(abs($settlement->rider_balance), 2) }} (رصيد المحفظة) @endif
                                @if ($settlement->company_benefits_total > 0) + {{ number_format($settlement->company_benefits_total, 2) }} (مزايا الشركة) @endif
                                @if ($settlement->company_deductions_total > 0) − {{ number_format($settlement->company_deductions_total, 2) }} (خصومات الشركة) @endif
                                = <strong>{{ number_format($settlement->net_salary, 2) }}</strong>
                            </div>
                        </div>
                    @endif

                    @if (!in_array($activeTab, ['imported','calculation','adjustments','summary','review','result']))
                        <div class="text-center py-4 text-muted">
                            <i class="la la-info-circle font-large-1"></i>
                            <p>الصفحة غير متاحة. <a href="{{ route('dashboard.monthly.periods.hungerstation.ftr.settlement.show', [$period, $settlement, 'tab' => 'imported']) }}">عودة للبيانات المستوردة</a></p>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    'use strict';

    var CSRF        = '{{ $csrfToken }}';
    var baseUrl     = '{{ $destroyBaseUrl }}';
    var isReadOnly  = {{ $isReadOnly ? 'true' : 'false' }};

    var benefitLabels = {
        @foreach (HungerStationFtrDelegateDeduction::BENEFIT_TYPE_LABELS as $k => $v)
            '{{ $k }}': '{{ $v }}',
        @endforeach
    };
    var deductionLabels = {
        @foreach (HungerStationFtrDelegateDeduction::DEDUCTION_TYPE_LABELS as $k => $v)
            '{{ $k }}': '{{ $v }}',
        @endforeach
    };
    var allLabels = Object.assign({}, benefitLabels, deductionLabels);

    // ── معاينة الراتب اللحظية ──────────────────────────────────────────
    var liveEl = document.getElementById('live-net-salary');
    if (!liveEl) return;

    var base = {
        distance:   parseFloat(liveEl.dataset.base)       || 0,
        penalties:  parseFloat(liveEl.dataset.penalties)  || 0,
        wallet:     parseFloat(liveEl.dataset.wallet)     || 0,
        benefits:   parseFloat(liveEl.dataset.benefits)   || 0,
        deductions: parseFloat(liveEl.dataset.deductions) || 0,
    };

    function updateLiveDisplay(benefitsTotal, deductionsTotal) {
        var net = base.distance - base.penalties - base.wallet + benefitsTotal - deductionsTotal;
        liveEl.textContent = net.toFixed(2) + ' ريال';
        liveEl.classList.remove('text-danger','text-success');
        liveEl.classList.add(net < 0 ? 'text-danger' : 'text-success');

        var hdr = document.getElementById('header-net-salary');
        if (hdr) {
            hdr.textContent = net.toFixed(2);
            hdr.classList.remove('text-danger','text-success');
            hdr.classList.add(net < 0 ? 'text-danger' : 'text-success');
        }

        var pb = document.getElementById('preview-benefits');
        var pd = document.getElementById('preview-deductions');
        if (pb) pb.textContent = benefitsTotal.toFixed(2);
        if (pd) pd.textContent = deductionsTotal.toFixed(2);
    }

    var curBenefits   = base.benefits;
    var curDeductions = base.deductions;

    // ── اختيار الاتجاه (نموذج الإضافة) ───────────────────────────────
    function setDirection(isBenefit) {
        document.getElementById('adj-is-benefit').value = isBenefit ? '1' : '0';
        var dedOpts = document.getElementById('adj-deduction-opts');
        var benOpts = document.getElementById('adj-benefit-opts');
        var select  = document.getElementById('adj-type-select');
        var btns    = document.querySelectorAll('.adj-dir-btn');

        btns.forEach(function(b) {
            var bv = parseInt(b.dataset.value);
            if (isBenefit) {
                b.className = b.className.replace(/btn-(outline-)?(danger|success)/g, '');
                b.classList.add(bv === 1 ? 'btn-success' : 'btn-outline-danger');
            } else {
                b.className = b.className.replace(/btn-(outline-)?(danger|success)/g, '');
                b.classList.add(bv === 0 ? 'btn-danger' : 'btn-outline-success');
            }
        });

        if (isBenefit) {
            dedOpts.style.display = 'none';
            benOpts.style.display = '';
            select.value = Object.keys(benefitLabels)[0] || '';
        } else {
            benOpts.style.display = 'none';
            dedOpts.style.display = '';
            select.value = Object.keys(deductionLabels)[0] || '';
        }
    }
    window.setDirection = setDirection;

    // ── AJAX: إضافة تسوية ──────────────────────────────────────────────
    var addForm = document.getElementById('add-adjustment-form');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('add-adj-btn');
            btn.disabled = true;

            fetch(addForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new FormData(addForm),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.error) { alert(data.error); return; }
                curBenefits   = parseFloat(data.company_benefits_total)   || 0;
                curDeductions = parseFloat(data.company_deductions_total) || 0;
                updateLiveDisplay(curBenefits, curDeductions);
                updateTotalsDisplay(curBenefits, curDeductions);
                rebuildTables(data.adjustments);
                addForm.reset();
                setDirection(0);
            })
            .catch(function() { addForm.submit(); })
            .finally(function() { btn.disabled = false; });
        });
    }

    // ── AJAX: حذف تسوية ────────────────────────────────────────────────
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.delete-adj-btn');
        if (!btn) return;
        if (!confirm('هل تريد حذف هذه التسوية؟')) return;

        var id  = btn.dataset.id;
        btn.disabled = true;

        fetch(baseUrl + id, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            curBenefits   = parseFloat(data.company_benefits_total)   || 0;
            curDeductions = parseFloat(data.company_deductions_total) || 0;
            updateLiveDisplay(curBenefits, curDeductions);
            updateTotalsDisplay(curBenefits, curDeductions);
            rebuildTables(data.adjustments);
        })
        .catch(function() { var row = document.getElementById('adj-row-' + id); if (row) row.remove(); })
        .finally(function() { btn.disabled = false; });
    });

    // ── AJAX: تعديل تسوية (نافذة) ─────────────────────────────────────
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.edit-adj-btn');
        if (!btn) return;

        var id        = btn.dataset.id;
        var isBenefit = parseInt(btn.dataset.isBenefit) === 1;
        var typeVal   = btn.dataset.type;
        var amount    = btn.dataset.amount;
        var notes     = btn.dataset.notes;

        var typeSelect = document.getElementById('edit-adj-type');
        typeSelect.innerHTML = '';
        var labels = isBenefit ? benefitLabels : deductionLabels;
        Object.keys(labels).forEach(function(k) {
            var opt = document.createElement('option');
            opt.value = k;
            opt.textContent = labels[k];
            if (k === typeVal) opt.selected = true;
            typeSelect.appendChild(opt);
        });

        document.getElementById('edit-adj-amount').value = amount;
        document.getElementById('edit-adj-notes').value  = notes;

        var form = document.getElementById('edit-adj-form');
        form.action = baseUrl.replace(/\/adjustments\/$/, '/adjustments/' + id);

        if (typeof $ !== 'undefined') {
            $('#editAdjModal').modal('show');
        }
    });

    var editForm = document.getElementById('edit-adj-form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('edit-adj-submit');
            btn.disabled = true;

            fetch(editForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new FormData(editForm),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.error) { alert(data.error); return; }
                curBenefits   = parseFloat(data.company_benefits_total)   || 0;
                curDeductions = parseFloat(data.company_deductions_total) || 0;
                updateLiveDisplay(curBenefits, curDeductions);
                updateTotalsDisplay(curBenefits, curDeductions);
                rebuildTables(data.adjustments);
                if (typeof $ !== 'undefined') $('#editAdjModal').modal('hide');
            })
            .catch(function() { editForm.submit(); })
            .finally(function() { btn.disabled = false; });
        });
    }

    // ── تحديث إجماليات رؤوس الأقسام ──────────────────────────────────
    function updateTotalsDisplay(benefitsTotal, deductionsTotal) {
        var bt = document.getElementById('benefits-total-display');
        var dt = document.getElementById('deductions-total-display');
        if (bt) bt.textContent = '+ ' + benefitsTotal.toFixed(2) + ' ريال';
        if (dt) dt.textContent = '− ' + deductionsTotal.toFixed(2) + ' ريال';
    }

    // ── إعادة بناء الجداول من بيانات الخادم ──────────────────────────
    function rebuildTables(adjustments) {
        var benefitRows   = (adjustments || []).filter(function(a) { return a.is_benefit; });
        var deductionRows = (adjustments || []).filter(function(a) { return !a.is_benefit; });

        rebuildSection('benefits',   benefitRows,   true);
        rebuildSection('deductions', deductionRows, false);
    }

    function rebuildSection(name, rows, isBenefit) {
        var wrap = document.getElementById(name + '-table-wrap');
        if (!wrap) return;

        if (!rows || rows.length === 0) {
            wrap.innerHTML = '<p class="text-muted text-center py-3 mb-0">لا توجد ' + (isBenefit ? 'مزايا' : 'خصومات') + ' مضافة.</p>';
            return;
        }

        var labels = isBenefit ? benefitLabels : deductionLabels;
        var sign   = isBenefit ? '+' : '−';
        var cls    = isBenefit ? 'text-success' : 'text-danger';

        var html = '<table class="table table-sm table-hover mb-0">'
            + '<thead class="thead-light"><tr><th>الصنف</th><th class="text-right">المبلغ</th><th>ملاحظة</th>'
            + (!isReadOnly ? '<th style="width:80px;"></th>' : '')
            + '</tr></thead><tbody id="' + name + '-tbody">';

        rows.forEach(function(a) {
            var typeLabel = labels[a.deduction_type] || allLabels[a.deduction_type] || a.deduction_type;
            html += '<tr id="adj-row-' + a.id + '">'
                + '<td>' + esc(typeLabel) + '</td>'
                + '<td class="text-right ' + cls + ' font-weight-bold">' + sign + ' ' + parseFloat(a.amount).toFixed(2) + '</td>'
                + '<td class="text-muted small">' + esc(a.notes || '—') + '</td>';

            if (!isReadOnly) {
                html += '<td>'
                    + '<button type="button" class="btn btn-xs btn-outline-primary edit-adj-btn mr-1"'
                    + ' data-id="' + a.id + '"'
                    + ' data-is-benefit="' + (a.is_benefit ? 1 : 0) + '"'
                    + ' data-type="' + esc(a.deduction_type) + '"'
                    + ' data-label="' + esc(a.label) + '"'
                    + ' data-amount="' + a.amount + '"'
                    + ' data-notes="' + esc(a.notes || '') + '"'
                    + ' title="تعديل"><i class="la la-edit"></i></button>'
                    + '<button type="button" class="btn btn-xs btn-outline-danger delete-adj-btn"'
                    + ' data-id="' + a.id + '"'
                    + ' title="حذف"><i class="la la-trash"></i></button>'
                    + '</td>';
            }

            html += '</tr>';
        });

        html += '</tbody></table>';
        wrap.innerHTML = html;
    }

    function esc(str) {
        return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

}());
</script>
@endsection
