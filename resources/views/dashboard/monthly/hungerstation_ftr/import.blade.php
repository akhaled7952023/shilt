@extends('layouts.dashboard.app')

@section('title') استيراد هنقرستيشن — {{ $period->getDisplayLabel() }} @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        {{-- Breadcrumb --}}
        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-10 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.welcome') }}">الرئيسية</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.monthly.periods.index') }}">الفترات الشهرية</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.monthly.periods.show', $period) }}">{{ $period->getDisplayLabel() }}</a>
                            </li>
                            <li class="breadcrumb-item active">استيراد هنقرستيشن</li>
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

            {{-- Period info bar --}}
            <div class="card mb-2">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <small class="text-muted d-block">الفترة</small>
                            <strong>{{ $period->getDisplayLabel() }}</strong>
                        </div>
                        <div class="col-md-4 text-center">
                            <small class="text-muted d-block">المنصة</small>
                            <strong>هنقرستيشن</strong>
                        </div>
                        <div class="col-md-4 text-left">
                            <small class="text-muted d-block">الحالة</small>
                            @if($period->isOpen())
                                <span class="badge badge-success"><i class="la la-unlock-alt"></i> مفتوح</span>
                            @else
                                <span class="badge badge-secondary"><i class="la la-lock"></i> مغلق</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Existing batch warning --}}
            @if ($existingBatch)
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div>
                            <i class="la la-exclamation-triangle mr-1"></i>
                            <strong>يوجد استيراد نشط لهذه الفترة</strong>
                            — {{ $existingBatch->original_filename }}
                            ({{ $existingBatch->matched_delegates }} مندوب)
                            بواسطة {{ $existingBatch->importedBy?->name ?? '—' }}
                            في {{ $existingBatch->imported_at?->format('Y-m-d H:i') ?? '—' }}.
                            <br>رفع ملف جديد سيستبدل البيانات الحالية.
                        </div>
                        <div class="mt-1">
                            <form method="POST"
                                  action="{{ route('dashboard.monthly.periods.hungerstation.ftr.import.delete', $period) }}"
                                  onsubmit="return confirm('هل تريد حذف بيانات الاستيراد الحالية نهائياً؟')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="la la-trash"></i> حذف الاستيراد الحالي
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            {{-- FTR notice --}}
            <div class="alert alert-info">
                <i class="la la-info-circle mr-1"></i>
                <strong>ملاحظة:</strong> يتم استيراد ورقة <code>RLVL</code> فقط من ملف الفاتورة.
                يتم تجاهل ورقة <code>WR</code> وجميع الأوراق الأخرى.
                يتطلب كل راكب أن يكون مسجلاً في النظام بـ <strong>معرف</strong> صحيح.
            </div>

            {{-- Upload form --}}
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">رفع ملف الفاتورة (RLVL)</h4>
                    <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">

                        <form method="POST"
                              action="{{ route('dashboard.monthly.periods.hungerstation.ftr.import.upload', $period) }}"
                              enctype="multipart/form-data"
                              id="ftr-upload-form">
                            @csrf

                            <div class="form-group">
                                <label class="d-block">اختر ملف Excel (.xlsx)</label>
                                <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                                    <label for="ftr_file_input"
                                           class="btn btn-outline-secondary mb-0"
                                           style="cursor:pointer; white-space:nowrap;">
                                        <i class="la la-folder-open"></i> استعراض...
                                    </label>
                                    <span id="ftr_file_name" class="text-muted small">لم يتم اختيار ملف</span>
                                    <input type="file"
                                           name="file"
                                           id="ftr_file_input"
                                           accept=".xlsx"
                                           required
                                           style="display:none;">
                                </div>
                                @error('file')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit"
                                    class="btn btn-primary"
                                    id="ftr_upload_btn">
                                <i class="la la-upload"></i> رفع الملف ومعاينة البيانات
                            </button>
                            <a href="{{ route('dashboard.monthly.periods.show', $period) }}"
                               class="btn btn-outline-secondary mr-1">إلغاء</a>
                        </form>

                    </div>
                </div>
            </div>

            {{-- History link --}}
            <div class="text-center mt-1">
                <a href="{{ route('dashboard.monthly.periods.hungerstation.ftr.import.history', $period) }}"
                   class="text-muted small">
                    <i class="la la-history"></i> عرض سجل الاستيرادات
                </a>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('ftr_file_input').addEventListener('change', function () {
    var nameEl = document.getElementById('ftr_file_name');
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

document.getElementById('ftr-upload-form').addEventListener('submit', function () {
    var btn = document.getElementById('ftr_upload_btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="la la-spinner la-spin"></i> جارٍ المعالجة...';
});
</script>
@endpush
@endsection
