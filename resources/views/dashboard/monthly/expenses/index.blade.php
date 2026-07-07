@extends('layouts.dashboard.app')

@section('title') مصروفات الشركة — {{ $period->getDisplayLabel() }} @endsection

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
                            <li class="breadcrumb-item active">مصروفات الشركة</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">

            @foreach(['success','error','info','warning'] as $flash)
                @if (session($flash))
                    <div class="alert alert-{{ $flash === 'error' ? 'danger' : $flash }} alert-dismissible fade show">
                        {{ session($flash) }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif
            @endforeach

            {{-- Period bar --}}
            <div class="card mb-2">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <small class="text-muted">الفترة</small>
                            <strong class="d-block">{{ $period->getDisplayLabel() }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">حالة الفترة</small>
                            <span class="d-block">
                                @if($period->isOpen())
                                    <span class="badge badge-success"><i class="la la-unlock-alt"></i> مفتوح</span>
                                @else
                                    <span class="badge badge-secondary"><i class="la la-lock"></i> مغلق</span>
                                @endif
                            </span>
                        </div>
                        <div class="col-md-3 text-left">
                            <small class="text-muted">إجمالي المصروفات</small>
                            <strong class="d-block text-danger">{{ number_format($total, 2) }} ريال</strong>
                        </div>
                    </div>
                </div>
            </div>

            @if($period->isClosed())
                <div class="alert alert-secondary">
                    <i class="la la-lock mr-1"></i>
                    <strong>هذه الفترة مغلقة</strong> — جميع البيانات في وضع القراءة فقط.
                </div>
            @endif

            <div class="row">

                {{-- Add expense form (open periods only) --}}
                @if($period->isOpen())
                <div class="col-md-4 mb-2">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="la la-plus-circle text-danger"></i> إضافة مصروف جديد
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST"
                                  action="{{ route('dashboard.monthly.periods.expenses.store', $period) }}">
                                @csrf
                                <div class="form-group">
                                    <label>الفئة <span class="text-danger">*</span></label>
                                    <input type="text"
                                           name="category"
                                           class="form-control @error('category') is-invalid @enderror"
                                           value="{{ old('category') }}"
                                           placeholder="مثال: صيانة، إيجار، وقود">
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>المبلغ (ريال) <span class="text-danger">*</span></label>
                                    <input type="number"
                                           name="amount"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount') }}"
                                           step="0.01"
                                           min="0.01">
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>ملاحظات</label>
                                    <textarea name="notes"
                                              class="form-control"
                                              rows="2"
                                              placeholder="وصف اختياري">{{ old('notes') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="la la-save"></i> حفظ المصروف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Expenses table --}}
                <div class="{{ $period->isOpen() ? 'col-md-8' : 'col-12' }} mb-2">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">
                                <i class="la la-list text-danger"></i>
                                مصروفات الشركة — {{ $period->getDisplayLabel() }}
                            </h5>
                            <span class="badge badge-danger">{{ $expenses->count() }} مصروف</span>
                        </div>
                        <div class="card-body p-0">
                            @if($expenses->isEmpty())
                                <div class="p-5 text-center text-muted">
                                    <i class="la la-inbox" style="font-size:2.5rem;"></i>
                                    <p class="mt-2">لا توجد مصروفات مسجلة لهذه الفترة.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>الفئة</th>
                                                <th class="text-left">المبلغ</th>
                                                <th>ملاحظات</th>
                                                <th>أضافه</th>
                                                <th class="text-center">التاريخ</th>
                                                @if($period->isOpen())
                                                    <th class="text-center">إجراء</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($expenses as $i => $expense)
                                                <tr>
                                                    <td class="text-muted small">{{ $i + 1 }}</td>
                                                    <td><strong>{{ $expense->category }}</strong></td>
                                                    <td class="text-left font-weight-bold text-danger">
                                                        {{ number_format($expense->amount, 2) }}
                                                    </td>
                                                    <td class="text-muted small">
                                                        {{ $expense->notes ?? '—' }}
                                                    </td>
                                                    <td class="small">
                                                        {{ $expense->createdBy?->name ?? '—' }}
                                                    </td>
                                                    <td class="text-center small">
                                                        {{ $expense->created_at->format('Y-m-d') }}
                                                    </td>
                                                    @if($period->isOpen())
                                                        <td class="text-center">
                                                            <button class="btn btn-sm btn-outline-primary"
                                                                    data-toggle="modal"
                                                                    data-target="#editModal{{ $expense->id }}">
                                                                <i class="la la-edit"></i>
                                                            </button>
                                                            <form method="POST"
                                                                  action="{{ route('dashboard.monthly.periods.expenses.destroy', [$period, $expense]) }}"
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('حذف هذا المصروف؟')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                        class="btn btn-sm btn-outline-danger">
                                                                    <i class="la la-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light font-weight-bold">
                                                <td colspan="{{ $period->isOpen() ? 2 : 2 }}">
                                                    الإجمالي ({{ $expenses->count() }})
                                                </td>
                                                <td class="text-left text-danger">
                                                    {{ number_format($total, 2) }} ريال
                                                </td>
                                                <td colspan="{{ $period->isOpen() ? 4 : 3 }}"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Edit modals --}}
@if($period->isOpen())
    @foreach($expenses as $expense)
    <div class="modal fade" id="editModal{{ $expense->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST"
                      action="{{ route('dashboard.monthly.periods.expenses.update', [$period, $expense]) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">تعديل المصروف</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>الفئة <span class="text-danger">*</span></label>
                            <input type="text" name="category" class="form-control"
                                   value="{{ $expense->category }}" required>
                        </div>
                        <div class="form-group">
                            <label>المبلغ (ريال) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control"
                                   value="{{ $expense->amount }}" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="2">{{ $expense->notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="la la-save"></i> حفظ التعديلات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endif

@endsection
