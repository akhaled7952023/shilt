@extends('layouts.dashboard.app')

@section('title') تعديل مندوب @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.delegates.index') }}">المناديب</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.delegates.show', $delegate) }}">{{ $delegate->name }}</a></li>
                            <li class="breadcrumb-item active">تعديل</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                    <div class="heading-elements">
                        <ul class="mb-0 list-inline">
                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                            <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                            <li><a data-action="close"><i class="ft-x"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">

                        @include('dashboard.includes.validations-errors')

                        <form class="form"
                              action="{{ route('dashboard.delegates.update', $delegate) }}"
                              method="POST"
                              enctype="multipart/form-data">
                            @csrf @method('PUT')

                            <div class="form-body">

                                <h4 class="form-section"><i class="la la-user"></i> المعلومات العامة</h4>
                                <div class="row">
                                    {{-- delegate_code يُولَّد تلقائياً ولا يظهر للمشرف --}}
                                    <input type="hidden" name="delegate_code" id="delegate_code_input"
                                           value="{{ old('delegate_code', $delegate->delegate_code) }}">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>الاسم <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control border-primary"
                                                   value="{{ old('name', $delegate->name) }}">
                                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label id="national_id_label">الهوية الوطنية</label>
                                            <input type="text" name="national_id" id="national_id_input"
                                                   class="form-control border-primary"
                                                   value="{{ old('national_id', $delegate->national_id) }}">
                                            <small class="text-muted" id="national_id_hint"></small>
                                            @error('national_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>رقم الجوال</label>
                                            <input type="text" name="phone" class="form-control border-primary"
                                                   value="{{ old('phone', $delegate->phone) }}">
                                            @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>المدينة</label>
                                            <select name="city_id" id="city_select" class="form-control border-primary">
                                                <option value=""></option>
                                                @foreach ($cities as $city)
                                                    <option value="{{ $city->id }}"
                                                        {{ old('city_id', $delegate->city_id) == $city->id ? 'selected' : '' }}>
                                                        {{ $city->getTranslation('name', 'ar') }} — {{ $city->getTranslation('name', 'en') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('city_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <h4 class="form-section"><i class="la la-mobile"></i> بيانات المنصة</h4>
                                @php
                                    $hsPlatform      = $platforms->firstWhere('code', 'hungerstation');
                                    $chefzPlatform   = $platforms->firstWhere('code', 'the-chefz');
                                    $hsPlatformId    = $hsPlatform?->id;
                                    $chefzPlatformId = $chefzPlatform?->id;
                                @endphp
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>المنصة</label>
                                            <select name="platform_id" id="platform_id_select" class="form-control border-primary">
                                                <option value="">بدون منصة</option>
                                                @foreach ($platforms as $platform)
                                                    <option value="{{ $platform->id }}"
                                                        {{ old('platform_id', $delegate->platform_id) == $platform->id ? 'selected' : '' }}>
                                                        {{ $platform->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('platform_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="hs_rider_id_field" style="display:none;">
                                        <div class="form-group">
                                            <label>المعرف <span class="text-danger">*</span></label>
                                            <input type="text" name="hungerstation_rider_id" id="hs_rider_id_input"
                                                   class="form-control border-primary"
                                                   value="{{ old('hungerstation_rider_id', $delegate->hungerstation_rider_id) }}"
                                                   placeholder="رقم التعريف في المنصة"
                                                   maxlength="20">
                                            @error('hungerstation_rider_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <script>
                                (function () {
                                    var hsPlatformId    = '{{ $hsPlatformId }}';
                                    var chefzPlatformId = '{{ $chefzPlatformId }}';
                                    var platformSelect  = document.getElementById('platform_id_select');
                                    var delegateCodeInput = document.getElementById('delegate_code_input');
                                    var riderIdField    = document.getElementById('hs_rider_id_field');
                                    var riderIdInput    = document.getElementById('hs_rider_id_input');
                                    var nationalIdInput = document.getElementById('national_id_input');
                                    var nationalIdLabel = document.getElementById('national_id_label');
                                    var nationalIdHint  = document.getElementById('national_id_hint');

                                    function syncChefzCode() {
                                        if (platformSelect.value == chefzPlatformId) {
                                            delegateCodeInput.value = nationalIdInput.value;
                                        }
                                    }

                                    function syncHsCode() {
                                        if (platformSelect.value == hsPlatformId) {
                                            var rid = riderIdInput.value.trim();
                                            delegateCodeInput.value = rid ? 'HS-' + rid : '';
                                        }
                                    }

                                    function toggle() {
                                        var isHs    = (platformSelect.value == hsPlatformId);
                                        var isChefz = (platformSelect.value == chefzPlatformId);

                                        // حقل المعرف: يظهر لهنقرستيشن فقط
                                        riderIdField.style.display = isHs ? '' : 'none';
                                        riderIdInput.required = isHs;

                                        // مزامنة كود المندوب المخفي
                                        if (isHs) {
                                            syncHsCode();
                                        } else if (isChefz) {
                                            syncChefzCode();
                                        }

                                        // تسمية الهوية الوطنية حسب المنصة
                                        if (isChefz) {
                                            nationalIdLabel.textContent = 'الهوية الوطنية (رمز الدخول للبوابة)';
                                            nationalIdHint.textContent  = 'يُستخدم تلقائياً ككود المندوب';
                                        } else {
                                            nationalIdLabel.textContent = 'الهوية الوطنية';
                                            nationalIdHint.textContent  = '';
                                        }
                                    }

                                    platformSelect.addEventListener('change', toggle);
                                    nationalIdInput.addEventListener('input', syncChefzCode);
                                    riderIdInput.addEventListener('input', syncHsCode);
                                    toggle();
                                }());
                                </script>

                                <h4 class="form-section"><i class="la la-bank"></i> البيانات البنكية</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>اسم البنك</label>
                                            <input type="text" name="bank_name" class="form-control border-primary"
                                                   value="{{ old('bank_name', $delegate->bank_name) }}" placeholder="اسم البنك">
                                            @error('bank_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>رقم الآيبان (IBAN)</label>
                                            <input type="text" name="iban" class="form-control border-primary"
                                                   value="{{ old('iban', $delegate->iban) }}" placeholder="SA...">
                                            @error('iban') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <h4 class="form-section"><i class="la la-camera"></i> الصور</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>الصورة الشخصية</label>
                                            <input type="file" name="profile_photo" class="dropify" accept="image/*"
                                                   @if($delegate->profile_photo) data-default-file="{{ Storage::url($delegate->profile_photo) }}" @endif>
                                            @error('profile_photo') <span class="text-danger d-block mt-1 small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>صورة الإقامة <small class="text-muted">(اختياري)</small></label>
                                            <input type="file" name="iqama_image" class="dropify" accept="image/*"
                                                   @if($delegate->iqama_image) data-default-file="{{ Storage::url($delegate->iqama_image) }}" @endif>
                                            @error('iqama_image') <span class="text-danger d-block mt-1 small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>صورة رخصة القيادة <small class="text-muted">(اختياري)</small></label>
                                            <input type="file" name="driving_license_image" class="dropify" accept="image/*"
                                                   @if($delegate->driving_license_image) data-default-file="{{ Storage::url($delegate->driving_license_image) }}" @endif>
                                            @error('driving_license_image') <span class="text-danger d-block mt-1 small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="form-actions right">
                                <a href="{{ route('dashboard.delegates.show', $delegate) }}" class="mr-1 btn btn-warning">
                                    <i class="ft-x"></i> إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="la la-check-square-o"></i> حفظ التعديلات
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('asset/dashboard/vendors/css/forms/selects/select2.min.css') }}">
<script src="{{ asset('asset/dashboard/vendors/js/forms/select/select2.full.min.js') }}"></script>
<script>
$(document).ready(function () {
    $('#city_select').select2({
        placeholder: 'اختر المدينة...',
        allowClear: true,
        dir: 'rtl',
        width: '100%',
        language: {
            noResults: function () { return 'لا توجد نتائج'; },
            searching: function () { return 'جارٍ البحث...'; }
        }
    });
});
</script>
@endsection
