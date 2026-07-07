@extends('layouts.dashboard.app')

@section('title') المناديب @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.welcome') }}">الرئيسية</a>
                            </li>
                            <li class="breadcrumb-item active">المناديب</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="card">
                <div class="card-header">
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

                <div class="card-content">
                    <div class="card-body">

                        {{-- فلتر البحث --}}
                        <form method="GET" action="{{ route('dashboard.delegates.index') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="بحث برقم المندوب أو الاسم أو الجوال"
                                           value="{{ $filters['search'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-control">
                                        <option value="">الحالة - الكل</option>
                                        <option value="active"     {{ ($filters['status'] ?? '') === 'active'     ? 'selected' : '' }}>نشط</option>
                                        <option value="inactive"   {{ ($filters['status'] ?? '') === 'inactive'   ? 'selected' : '' }}>غير نشط</option>
                                        <option value="suspended"  {{ ($filters['status'] ?? '') === 'suspended'  ? 'selected' : '' }}>موقوف</option>
                                        <option value="terminated" {{ ($filters['status'] ?? '') === 'terminated' ? 'selected' : '' }}>منتهي</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="city_id" class="form-control">
                                        <option value="">المدينة - الكل</option>
                                        @foreach ($cities as $city)
                                            <option value="{{ $city->id }}"
                                                {{ ($filters['city_id'] ?? '') == $city->id ? 'selected' : '' }}>
                                                {{ $city->getTranslation('name', 'ar') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="platform_id" class="form-control">
                                        <option value="">المنصة - الكل</option>
                                        @foreach ($platforms as $platform)
                                            <option value="{{ $platform->id }}"
                                                {{ ($filters['platform_id'] ?? '') == $platform->id ? 'selected' : '' }}>
                                                {{ $platform->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-secondary">بحث</button>
                                    <a href="{{ route('dashboard.delegates.index') }}" class="btn btn-light">×</a>
                                </div>
                            </div>
                        </form>

                        <div class="mb-2 text-left">
                            @can('create', App\Models\Delegate::class)
                                <a href="{{ route('dashboard.delegates.create') }}" class="btn btn-primary btn-sm">
                                    <i class="la la-plus"></i> إضافة مندوب
                                </a>
                            @endcan
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>كود المندوب</th>
                                        <th>الاسم</th>
                                        <th>المنصة</th>
                                        <th>الجوال</th>
                                        <th>المدينة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($delegates as $delegate)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $delegate->delegate_code ?? '—' }}</strong></td>
                                            <td>
                                                <a href="{{ route('dashboard.delegates.show', $delegate) }}">
                                                    {{ $delegate->name }}
                                                </a>
                                            </td>
                                            <td>
                                                @if ($delegate->platform?->code === 'hungerstation')
                                                    <span class="badge" style="background-color:#fef9e7;color:#92611a;font-size:0.8rem;padding:4px 8px;border-radius:4px;">
                                                        {{ $delegate->platform->name }}
                                                    </span>
                                                @elseif ($delegate->platform?->code === 'the-chefz')
                                                    <span class="badge" style="background-color:#fff0e6;color:#c2510f;font-size:0.8rem;padding:4px 8px;border-radius:4px;">
                                                        {{ $delegate->platform->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $delegate->phone ?? '—' }}</td>
                                            <td>{{ $delegate->city?->getTranslation('name', 'ar') ?? '—' }}</td>
                                            <td>
                                                <a href="{{ route('dashboard.delegates.show', $delegate) }}"
                                                   class="btn btn-sm btn-info" title="عرض">
                                                    <i class="la la-eye"></i>
                                                </a>
                                                @can('update', $delegate)
                                                    <a href="{{ route('dashboard.delegates.edit', $delegate) }}"
                                                       class="btn btn-sm btn-warning" title="تعديل">
                                                        <i class="la la-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $delegate)
                                                    <form action="{{ route('dashboard.delegates.destroy', $delegate) }}"
                                                          method="POST" class="d-inline-block"
                                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا المندوب؟')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                            <i class="la la-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">لا توجد بيانات</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $delegates->appends($filters)->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
