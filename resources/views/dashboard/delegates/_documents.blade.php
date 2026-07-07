@php
    $iqamaDoc   = $delegate->delegateDocuments->first(fn($d) => $d->document_type_id === ($iqamaType?->id));
    $licenseDoc = $delegate->delegateDocuments->first(fn($d) => $d->document_type_id === ($licenseType?->id));
    $docService = app(\App\Services\Dashboard\Delegates\IDelegateDocumentService::class);
@endphp

@include('dashboard.includes.validations-errors')

{{-- قسم الإقامة --}}
<div class="mb-4">
    <h6 class="font-weight-bold mb-2"><i class="la la-id-card text-primary"></i> الإقامة</h6>

    @if ($iqamaDoc)
        <div class="p-3 mb-2 border rounded">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted d-block">رقم الإقامة</small>
                    <strong>{{ $iqamaDoc->document_number ?? '—' }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">تاريخ الإصدار</small>
                    {{ $iqamaDoc->issue_date?->format('Y-m-d') ?? '—' }}
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">تاريخ الانتهاء</small>
                    {{ $iqamaDoc->expiry_date?->format('Y-m-d') ?? '—' }}
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">الحالة</small>
                    @include('dashboard.components._status_badge', ['status' => $docService->getExpiryStatus($iqamaDoc)])
                </div>
                <div class="col-md-1 text-left">
                    @can('update', $delegate)
                        <a href="javascript:void(0)"
                           class="btn btn-sm btn-outline-danger"
                           onclick="if(confirm('هل أنت متأكد من حذف هذه الوثيقة؟')){document.getElementById('del-doc-{{ $iqamaDoc->id }}').submit();} return false">
                            <i class="la la-trash"></i>
                        </a>
                        <form id="del-doc-{{ $iqamaDoc->id }}"
                              action="{{ route('dashboard.delegates.documents.destroy', [$delegate, $iqamaDoc]) }}"
                              method="POST" style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    @else
        <p class="text-muted small">لا توجد إقامة مسجلة.</p>
    @endif

    @can('update', $delegate)
        @if ($iqamaType)
            <div class="mt-2">
                <a data-toggle="collapse" href="#iqama-form" class="btn btn-sm btn-outline-primary">
                    <i class="la la-plus"></i> {{ $iqamaDoc ? 'إضافة سجل آخر' : 'إضافة إقامة' }}
                </a>
                <div class="collapse mt-2" id="iqama-form">
                    <form action="{{ route('dashboard.delegates.documents.store', $delegate) }}"
                          method="POST">
                        @csrf
                        <input type="hidden" name="document_type_id" value="{{ $iqamaType->id }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small">رقم الإقامة</label>
                                    <input type="text" name="document_number" class="form-control form-control-sm border-primary"
                                           value="{{ old('document_number') }}" placeholder="رقم الإقامة">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small">تاريخ الإصدار</label>
                                    <input type="date" name="issue_date" class="form-control form-control-sm border-primary"
                                           value="{{ old('issue_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small">تاريخ الانتهاء</label>
                                    <input type="date" name="expiry_date" class="form-control form-control-sm border-primary"
                                           value="{{ old('expiry_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                                        <i class="la la-save"></i> حفظ
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endcan
</div>

<hr>

{{-- قسم رخصة القيادة --}}
<div class="mb-2">
    <h6 class="font-weight-bold mb-2"><i class="la la-car text-primary"></i> رخصة القيادة</h6>

    @if ($licenseDoc)
        <div class="p-3 mb-2 border rounded">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted d-block">رقم الرخصة</small>
                    <strong>{{ $licenseDoc->document_number ?? '—' }}</strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">تاريخ الإصدار</small>
                    {{ $licenseDoc->issue_date?->format('Y-m-d') ?? '—' }}
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">تاريخ الانتهاء</small>
                    {{ $licenseDoc->expiry_date?->format('Y-m-d') ?? '—' }}
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">الحالة</small>
                    @include('dashboard.components._status_badge', ['status' => $docService->getExpiryStatus($licenseDoc)])
                </div>
                <div class="col-md-1 text-left">
                    @can('update', $delegate)
                        <a href="javascript:void(0)"
                           class="btn btn-sm btn-outline-danger"
                           onclick="if(confirm('هل أنت متأكد من حذف هذه الوثيقة؟')){document.getElementById('del-doc-{{ $licenseDoc->id }}').submit();} return false">
                            <i class="la la-trash"></i>
                        </a>
                        <form id="del-doc-{{ $licenseDoc->id }}"
                              action="{{ route('dashboard.delegates.documents.destroy', [$delegate, $licenseDoc]) }}"
                              method="POST" style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    @else
        <p class="text-muted small">لا توجد رخصة قيادة مسجلة.</p>
    @endif

    @can('update', $delegate)
        @if ($licenseType)
            <div class="mt-2">
                <a data-toggle="collapse" href="#license-form" class="btn btn-sm btn-outline-primary">
                    <i class="la la-plus"></i> {{ $licenseDoc ? 'إضافة سجل آخر' : 'إضافة رخصة قيادة' }}
                </a>
                <div class="collapse mt-2" id="license-form">
                    <form action="{{ route('dashboard.delegates.documents.store', $delegate) }}"
                          method="POST">
                        @csrf
                        <input type="hidden" name="document_type_id" value="{{ $licenseType->id }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small">رقم الرخصة</label>
                                    <input type="text" name="document_number" class="form-control form-control-sm border-primary"
                                           value="{{ old('document_number') }}" placeholder="رقم رخصة القيادة">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small">تاريخ الإصدار</label>
                                    <input type="date" name="issue_date" class="form-control form-control-sm border-primary"
                                           value="{{ old('issue_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small">تاريخ الانتهاء</label>
                                    <input type="date" name="expiry_date" class="form-control form-control-sm border-primary"
                                           value="{{ old('expiry_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                                        <i class="la la-save"></i> حفظ
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endcan
</div>
