@extends('layouts.dashboard.app')

@section('title') سجل استيراد شيفز — {{ $period->getDisplayLabel() }} @endsection

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
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.monthly.periods.chefz.import', $period) }}">استيراد شيفز</a>
                            </li>
                            <li class="breadcrumb-item active">السجل الكامل</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">

            {{-- Summary bar --}}
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <small class="text-muted">الفترة</small>
                            <strong class="d-block">{{ $period->getDisplayLabel() }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">إجمالي الملفات</small>
                            <strong class="d-block text-success">{{ $batches->count() }} ملف</strong>
                        </div>
                        <div class="col-md-3 text-left">
                            <small class="text-muted">إجمالي الطلبات</small>
                            <strong class="d-block text-primary">{{ number_format($batches->sum('total_rows')) }} طلب</strong>
                        </div>
                    </div>
                </div>
            </div>

            @if($batches->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="la la-inbox" style="font-size:2.5rem;"></i>
                    <p class="mt-2">لا يوجد سجل استيراد بعد.</p>
                </div>
            @else
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>اسم الملف</th>
                                        <th class="text-center">طلبات مضافة</th>
                                        <th class="text-center">تخطّى (مكررة)</th>
                                        <th class="text-center">مناديب</th>
                                        <th class="text-center">منشأون جدد</th>
                                        <th class="text-center">حذف</th>
                                        <th>تاريخ الاستيراد</th>
                                        <th>بواسطة</th>
                                        <th class="text-center">المدة (ms)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batches as $batch)
                                        <tr>
                                            <td>
                                                <span class="badge badge-{{ $batch->status === 'completed' ? 'success' : 'secondary' }}">
                                                    {{ $batch->version_number }}
                                                </span>
                                            </td>
                                            <td class="text-monospace small" style="max-width:220px;word-break:break-all;">
                                                {{ $batch->original_filename }}
                                            </td>
                                            <td class="text-center font-weight-bold">
                                                {{ number_format($batch->total_rows ?? 0) }}
                                            </td>
                                            <td class="text-center text-muted">
                                                {{ number_format($batch->skipped_duplicates ?? 0) }}
                                            </td>
                                            <td class="text-center">
                                                {{ number_format($batch->unique_delegates ?? 0) }}
                                            </td>
                                            <td class="text-center text-warning">
                                                {{ number_format($batch->new_delegates_created ?? 0) }}
                                            </td>
                                            <td class="text-center text-muted">
                                                {{ number_format($batch->error_count ?? 0) }}
                                            </td>
                                            <td class="small">
                                                {{ $batch->imported_at?->format('Y-m-d H:i') ?? '—' }}
                                            </td>
                                            <td>{{ $batch->importedBy?->name ?? '—' }}</td>
                                            <td class="text-center text-muted small">
                                                {{ $batch->import_duration_ms ? number_format($batch->import_duration_ms).' ms' : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="2">الإجمالي</td>
                                        <td class="text-center">{{ number_format($batches->sum('total_rows')) }}</td>
                                        <td class="text-center">{{ number_format($batches->sum('skipped_duplicates')) }}</td>
                                        <td colspan="6"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
