@extends('layouts.dashboard.app')

@section('title') إيجارات المركبة @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.vehicles.index') }}">المركبات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.vehicles.show', $vehicle) }}">{{ $vehicle->plate_number }}</a></li>
                            <li class="breadcrumb-item active">الإيجارات</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">إيجارات المركبة — {{ $vehicle->plate_number }}</h4>
                    <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                    <div class="heading-elements">
                        <ul class="mb-0 list-inline">
                            @can('update', $vehicle)
                                <li>
                                    <a href="{{ route('dashboard.vehicles.rentals.create', $vehicle) }}"
                                       class="btn btn-primary btn-sm">
                                        <i class="la la-plus"></i> إضافة إيجار
                                    </a>
                                </li>
                            @endcan
                            <li>
                                <a href="{{ route('dashboard.vehicles.show', $vehicle) }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="la la-arrow-right"></i> العودة للملف
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <table class="table table-responsive-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الفترة</th>
                                    <th>المبلغ</th>
                                    <th>الجهة الدافعة</th>
                                    <th>المندوب</th>
                                    <th>ملاحظات</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rentals as $rental)
                                    <tr>
                                        <th>{{ $loop->iteration }}</th>
                                        <td>
                                            @if ($rental->monthlyPeriod)
                                                {{ $rental->monthlyPeriod->year }}/{{ str_pad($rental->monthlyPeriod->month, 2, '0', STR_PAD_LEFT) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ number_format($rental->amount, 2) }} ر.س</td>
                                        <td>
                                            @if ($rental->payment_by->value === 'company')
                                                <span class="badge badge-secondary">الشركة</span>
                                            @else
                                                <span class="badge badge-primary">مندوب</span>
                                            @endif
                                        </td>
                                        <td>{{ $rental->delegate?->name ?? '—' }}</td>
                                        <td>{{ $rental->notes ?? '—' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('update', $vehicle)
                                                    <a href="{{ route('dashboard.vehicles.rentals.edit', [$vehicle, $rental]) }}"
                                                       class="btn btn-sm btn-info">
                                                        <i class="la la-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0)"
                                                       class="btn btn-sm btn-danger"
                                                       onclick="if(confirm('هل أنت متأكد من حذف هذا الإيجار؟')){document.getElementById('del-rental-{{ $rental->id }}').submit();} return false">
                                                        <i class="la la-trash"></i>
                                                    </a>
                                                @endcan
                                            </div>
                                            <form id="del-rental-{{ $rental->id }}"
                                                  action="{{ route('dashboard.vehicles.rentals.destroy', [$vehicle, $rental]) }}"
                                                  method="POST" style="display:none;">
                                                @csrf @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">لا توجد إيجارات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{ $rentals->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
