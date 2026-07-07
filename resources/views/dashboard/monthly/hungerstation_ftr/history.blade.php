@extends('layouts.dashboard.app')

@section('title') سجل الاستيرادات — هنقرستيشن — {{ $period->getDisplayLabel() }} @endsection

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
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.monthly.periods.hungerstation.ftr.import', $period) }}">استيراد هنقرستيشن</a></li>
                            <li class="breadcrumb-item active">السجل</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">سجل الاستيرادات — {{ $period->getDisplayLabel() }}</h4>
                </div>
                <div class="card-body p-0">
                    @if ($batches->isEmpty())
                        <div class="p-2 text-muted">لا يوجد سجل استيراد لهذه الفترة.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>اسم الملف</th>
                                        <th class="text-center">الركاب</th>
                                        <th class="text-center">المطابقون</th>
                                        <th class="text-right">الدفع الأساسي</th>
                                        <th class="text-right">مدفوعات المسافات</th>
                                        <th>الحالة</th>
                                        <th>بواسطة</th>
                                        <th>التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($batches as $batch)
                                        <tr>
                                            <td>{{ $batch->id }}</td>
                                            <td>
                                                <span title="{{ $batch->original_filename }}">
                                                    {{ Str::limit($batch->original_filename, 40) }}
                                                </span>
                                            </td>
                                            <td class="text-center">{{ $batch->total_riders }}</td>
                                            <td class="text-center">{{ $batch->matched_delegates }}</td>
                                            <td class="text-right">{{ number_format($batch->basic_payment_total, 2) }}</td>
                                            <td class="text-right">{{ number_format($batch->distance_payment_total, 2) }}</td>
                                            <td>
                                                @if ($batch->status === 'completed')
                                                    <span class="badge badge-success">مكتمل</span>
                                                @elseif ($batch->status === 'failed')
                                                    <span class="badge badge-danger">فشل</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $batch->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $batch->importedBy?->name ?? '—' }}</td>
                                            <td>{{ $batch->imported_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-1">
                <a href="{{ route('dashboard.monthly.periods.hungerstation.ftr.import', $period) }}"
                   class="btn btn-outline-secondary btn-sm">
                    <i class="la la-arrow-right"></i> العودة للاستيراد
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
