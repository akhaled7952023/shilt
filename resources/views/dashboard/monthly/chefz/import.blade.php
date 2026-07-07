@extends('layouts.dashboard.app')

@section('title') استيراد بيانات شيفز — {{ $period->getDisplayLabel() }} @endsection

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
                            <li class="breadcrumb-item active">استيراد شيفز</li>
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

            {{-- Payout status bar --}}
            <div class="card mb-2">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <small class="text-muted d-block">الفترة</small>
                            <strong>{{ $period->getDisplayLabel() }}</strong>
                        </div>
                        <div class="col-md-4 text-center">
                            <small class="text-muted d-block">الدفعة الأولى</small>
                            @if ($payout1Batch)
                                <span class="badge badge-success"><i class="la la-check"></i> مستوردة</span>
                                <small class="text-muted d-block">{{ $payout1Batch->total_rows }} طلب</small>
                            @else
                                <span class="badge badge-warning"><i class="la la-clock-o"></i> لم تُستورد</span>
                            @endif
                        </div>
                        <div class="col-md-4 text-center">
                            <small class="text-muted d-block">الدفعة الثانية</small>
                            @if ($payout2Batch)
                                <span class="badge badge-success"><i class="la la-check"></i> مستوردة</span>
                                <small class="text-muted d-block">{{ $payout2Batch->total_rows }} طلب</small>
                            @else
                                <span class="badge badge-secondary"><i class="la la-clock-o"></i> لم تُستورد</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($isMonthComplete)
                <div class="alert alert-success">
                    <i class="la la-check-circle"></i>
                    <strong>الشهر مكتمل</strong> — تم استيراد الدفعتين لهذه الفترة.
                    يمكنك إعادة استيراد أي دفعة لاستبدال بياناتها.
                </div>
            @else
                <div class="alert alert-info">
                    <i class="la la-info-circle"></i>
                    شيفز يُنتج <strong>دفعتين شهريتين</strong>.
                    ارفع كل دفعة على حدة وحدد رقمها قبل الرفع.
                    إعادة رفع دفعة موجودة تستبدل بياناتها كاملاً (لا تُلغي الدفعة الأخرى).
                </div>
            @endif

            {{-- Upload form --}}
            @if($period->isOpen())
                <div class="card mb-3">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="la la-upload text-primary"></i> رفع ملف دفعة
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST"
                              action="{{ route('dashboard.monthly.periods.chefz.import.upload', $period) }}"
                              enctype="multipart/form-data"
                              id="chefz-upload-form">
                            @csrf

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold">رقم الدفعة <span class="text-danger">*</span></label>
                                        <div class="btn-group d-block" role="group">
                                            <label class="btn btn-outline-primary {{ $payout1Batch ? 'btn-outline-secondary' : '' }} mb-0"
                                                   style="cursor:pointer;">
                                                <input type="radio" name="payout_number" value="1"
                                                       style="display:none;" required>
                                                <i class="la la-1x la-circle{{ $payout1Batch ? '' : '-o' }}"></i>
                                                الدفعة الأولى
                                                @if ($payout1Batch)
                                                    <br><small class="d-block text-warning">(إعادة رفع — استبدال)</small>
                                                @endif
                                            </label>
                                            <label class="btn btn-outline-primary mb-0"
                                                   style="cursor:pointer;">
                                                <input type="radio" name="payout_number" value="2"
                                                       style="display:none;">
                                                <i class="la la-2x la-circle{{ $payout2Batch ? '' : '-o' }}"></i>
                                                الدفعة الثانية
                                                @if ($payout2Batch)
                                                    <br><small class="d-block text-warning">(إعادة رفع — استبدال)</small>
                                                @endif
                                            </label>
                                        </div>
                                        @error('payout_number')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">ملف Excel (.xlsx) <span class="text-danger">*</span></label>
                                        <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                                            <label for="chefz_file_input"
                                                   class="btn btn-outline-secondary mb-0"
                                                   style="cursor:pointer; white-space:nowrap;">
                                                <i class="la la-folder-open"></i> استعراض...
                                            </label>
                                            <span id="chefz_file_name" class="text-muted small">لم يتم اختيار ملف</span>
                                            <input type="file"
                                                   name="file"
                                                   id="chefz_file_input"
                                                   accept=".xlsx"
                                                   required
                                                   style="display:none;">
                                        </div>
                                        @error('file')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary mb-3 w-100" id="chefz_upload_btn">
                                        <i class="la la-upload"></i> رفع وتحليل
                                    </button>
                                </div>
                            </div>

                            <p class="text-muted small mb-0">
                                أعمدة مطلوبة:
                                <code>Chef Order ID</code>, <code>Driver ID</code>,
                                <code>Driver Name</code>, <code>Date</code>,
                                <code>Driver Delivery Fees</code>
                            </p>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-secondary">
                    <i class="la la-lock"></i> <strong>الفترة مغلقة</strong> — لا يمكن رفع ملفات جديدة.
                </div>
            @endif

            {{-- Payout batches summary --}}
            @if($batches->isNotEmpty())
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0">
                            <i class="la la-history text-success"></i>
                            الدفعات المستوردة
                        </h4>
                        <a href="{{ route('dashboard.monthly.periods.chefz.import.history', $period) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="la la-list"></i> سجل مفصّل
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>الدفعة</th>
                                    <th>اسم الملف</th>
                                    <th class="text-center">طلبات</th>
                                    <th class="text-center">مناديب</th>
                                    <th>تاريخ الاستيراد</th>
                                    <th>بواسطة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batches as $batch)
                                    <tr>
                                        <td>
                                            <span class="badge badge-{{ $batch->payout_number == 1 ? 'primary' : 'info' }}">
                                                {{ $batch->getPayoutLabel() }}
                                            </span>
                                        </td>
                                        <td class="text-monospace small">{{ Str::limit($batch->original_filename, 35) }}</td>
                                        <td class="text-center">{{ number_format($batch->total_rows) }}</td>
                                        <td class="text-center">{{ number_format($batch->unique_delegates ?? 0) }}</td>
                                        <td>{{ $batch->imported_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                        <td>{{ $batch->importedBy?->name ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                @if($period->isOpen())
                    <div class="text-center text-muted py-4">
                        <i class="la la-cloud-upload" style="font-size:2.5rem;"></i>
                        <p class="mt-2">لم يتم استيراد أي دفعة بعد لهذه الفترة.</p>
                    </div>
                @endif
            @endif

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Payout radio buttons styled as toggle
document.querySelectorAll('input[name="payout_number"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('label.btn').forEach(function(l) {
            l.classList.remove('active', 'btn-primary');
            l.classList.add('btn-outline-primary');
        });
        if (this.checked) {
            this.closest('label').classList.add('active', 'btn-primary');
            this.closest('label').classList.remove('btn-outline-primary');
        }
    });
});

// File name display
document.getElementById('chefz_file_input').addEventListener('change', function() {
    var nameEl = document.getElementById('chefz_file_name');
    if (this.files.length) {
        nameEl.textContent = this.files[0].name;
        nameEl.style.color = '#1cc88a';
        nameEl.style.fontWeight = '600';
    } else {
        nameEl.textContent = 'لم يتم اختيار ملف';
        nameEl.style.color = '';
        nameEl.style.fontWeight = '';
    }
});

// Submit guard
document.getElementById('chefz-upload-form').addEventListener('submit', function() {
    var payout = document.querySelector('input[name="payout_number"]:checked');
    if (!payout) {
        alert('يرجى تحديد رقم الدفعة أولاً (الأولى أو الثانية)');
        return false;
    }
    var btn = document.getElementById('chefz_upload_btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="la la-spinner la-spin"></i> جارٍ المعالجة...';
});
</script>
@endsection
