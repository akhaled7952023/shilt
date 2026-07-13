@extends('layouts.portal.app')
@section('title') {{ __('portal.ticket_step2_title') }} @endsection

@section('content')
<div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
    <a href="{{ route('portal.support.tickets.create') }}"
       style="width:36px; height:36px; border-radius:9px; background:white; border:1.5px solid var(--border);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--muted);
              flex-shrink:0;">
        <i class="la la-arrow-{{ app()->isLocale('ar') ? 'right' : 'left' }}" style="font-size:20px;"></i>
    </a>
    <div>
        <div style="font-size:12px; color:var(--muted); margin-bottom:1px;">{{ __('portal.ticket_new_title') }}</div>
        <h1 style="font-size:18px; font-weight:800; color:var(--text); margin:0;">
            {{ __('portal.ticket_step2_title') }}
        </h1>
    </div>
</div>

{{-- Category badge + change link --}}
<div class="p-card" style="padding:12px 16px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between;">
    <div>
        <div style="font-size:11px; color:var(--muted); margin-bottom:2px;">{{ __('portal.ticket_form_category_label') }}</div>
        <div style="font-size:14px; font-weight:700; color:var(--text);">
            @lang('portal.ticket_cat_' . $category->value)
        </div>
    </div>
    <a href="{{ route('portal.support.tickets.create') }}"
       style="font-size:12px; color:var(--primary); text-decoration:none; font-weight:600;">
        {{ __('portal.ticket_form_change_category') }}
    </a>
</div>

{{-- Financial request info banner --}}
@if($category->isFinancial())
    <div style="background:#fef3c7; border:1px solid #fbbf24; border-radius:10px; padding:12px 14px; margin-bottom:16px;
                display:flex; align-items:flex-start; gap:8px; font-size:13px; color:#92400e;">
        <i class="la la-info-circle" style="font-size:18px; flex-shrink:0; margin-top:1px;"></i>
        <span>{{ __('portal.ticket_cat_desc_' . $category->value) }}</span>
    </div>
@endif

<form method="POST" action="{{ route('portal.support.tickets.store') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="category" value="{{ $category->value }}">

    {{-- Subject --}}
    <div style="margin-bottom:16px;">
        <label style="display:block; font-size:13px; font-weight:700; color:var(--text); margin-bottom:6px;">
            {{ __('portal.ticket_form_subject_label') }} <span style="color:var(--danger);">*</span>
        </label>
        <input type="text" name="subject" class="portal-input @error('subject') is-invalid @enderror"
               placeholder="{{ __('portal.ticket_form_subject_ph') }}"
               value="{{ old('subject') }}" maxlength="255" required>
        @error('subject')
            <div style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</div>
        @enderror
    </div>

    {{-- Description --}}
    <div style="margin-bottom:16px;">
        <label style="display:block; font-size:13px; font-weight:700; color:var(--text); margin-bottom:6px;">
            {{ __('portal.ticket_form_description_label') }}
            <span style="color:var(--muted); font-size:11px; font-weight:400;">{{ __('portal.ticket_form_optional') }}</span>
        </label>
        <textarea name="description"
                  class="portal-input @error('description') is-invalid @enderror"
                  placeholder="{{ __('portal.ticket_form_description_ph') }}"
                  rows="5" maxlength="5000"
                  style="resize:vertical;">{{ old('description') }}</textarea>
        @error('description')
            <div style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</div>
        @enderror
        <div id="descCount" style="font-size:11px; color:var(--muted); margin-top:4px; text-align:end;">
            <span id="descLen">0</span> / 5000
        </div>
    </div>

    {{-- Requested Amount (financial only) --}}
    @if($category->isFinancial())
        <div style="margin-bottom:16px;" id="amountField">
            <label style="display:block; font-size:13px; font-weight:700; color:var(--text); margin-bottom:6px;">
                {{ __('portal.ticket_form_amount_label') }} <span style="color:var(--danger);">*</span>
            </label>
            <input type="number" name="requested_amount"
                   class="portal-input @error('requested_amount') is-invalid @enderror"
                   placeholder="{{ __('portal.ticket_form_amount_ph') }}"
                   value="{{ old('requested_amount') }}"
                   step="0.01" min="0.01" max="999999.99" required>
            @error('requested_amount')
                <div style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
    @endif

    {{-- Attachments --}}
    <div style="margin-bottom:24px;">
        <label style="display:block; font-size:13px; font-weight:700; color:var(--text); margin-bottom:6px;">
            {{ __('portal.ticket_form_attachments_label') }}
        </label>
        <input type="file" name="attachments[]"
               class="portal-input @error('attachments') is-invalid @enderror @error('attachments.*') is-invalid @enderror"
               accept=".jpg,.jpeg,.png,.webp,.pdf" multiple
               style="padding:10px; cursor:pointer;">
        <div style="font-size:11px; color:var(--muted); margin-top:4px;">
            {{ __('portal.ticket_form_attachments_hint') }}
        </div>
        @error('attachments')
            <div style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</div>
        @enderror
        @error('attachments.*')
            <div style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="portal-btn portal-btn-primary">
        {{ __('portal.ticket_form_submit') }}
    </button>
</form>

@push('scripts')
<script>
    const ta = document.querySelector('textarea[name="description"]');
    const len = document.getElementById('descLen');
    if (ta && len) {
        const update = () => { len.textContent = ta.value.length; };
        ta.addEventListener('input', update);
        update();
    }
</script>
@endpush
@endsection
