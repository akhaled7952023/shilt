{{--
  Partial: ticket queue filter bar
  Variables: $filters, $adminUsers, $periods, $categories, $priorities, $sources
--}}
<form method="GET" action="{{ route('dashboard.support.tickets.index') }}" class="mb-2">
    <div class="row align-items-end">

        <div class="col-md-3">
            <label class="small text-muted">بحث</label>
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="رقم التذكرة، الموضوع، اسم المندوب..."
                   value="{{ $filters['search'] ?? '' }}"
                   minlength="2">
        </div>

        <div class="col-md-2">
            <label class="small text-muted">الحالة</label>
            <select name="status" class="form-control form-control-sm">
                <option value="">كل الحالات</option>
                @foreach(\App\Enums\TicketStatus::cases() as $s)
                    <option value="{{ $s->value }}"
                        {{ ($filters['status'] ?? '') === $s->value ? 'selected' : '' }}>
                        {{ $s->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="small text-muted">التصنيف</label>
            <select name="category" class="form-control form-control-sm">
                <option value="">كل التصنيفات</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->value }}"
                        {{ ($filters['category'] ?? '') === $cat->value ? 'selected' : '' }}>
                        {{ $cat->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="small text-muted">الأولوية</label>
            <select name="priority" class="form-control form-control-sm">
                <option value="">كل الأولويات</option>
                @foreach($priorities as $pr)
                    <option value="{{ $pr->value }}"
                        {{ ($filters['priority'] ?? '') === $pr->value ? 'selected' : '' }}>
                        {{ $pr->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="small text-muted">SLA</label>
            <select name="sla_status" class="form-control form-control-sm">
                <option value="">الكل</option>
                <option value="overdue_response"   {{ ($filters['sla_status'] ?? '') === 'overdue_response'   ? 'selected' : '' }}>تأخر الرد الأول</option>
                <option value="overdue_resolution" {{ ($filters['sla_status'] ?? '') === 'overdue_resolution' ? 'selected' : '' }}>تأخر الحل</option>
            </select>
        </div>

        {{-- Assignment filter removed: collaborative model, no ownership --}}

        <div class="col-md-2 mt-1">
            <label class="small text-muted">من تاريخ</label>
            <input type="date" name="date_from" class="form-control form-control-sm"
                   value="{{ $filters['date_from'] ?? '' }}">
        </div>

        <div class="col-md-2 mt-1">
            <label class="small text-muted">إلى تاريخ</label>
            <input type="date" name="date_to" class="form-control form-control-sm"
                   value="{{ $filters['date_to'] ?? '' }}">
        </div>

        <div class="col-md-2 mt-1">
            <label class="small text-muted">الفترة الشهرية</label>
            <select name="period_id" class="form-control form-control-sm">
                <option value="">كل الفترات</option>
                @foreach($periods as $period)
                    <option value="{{ $period->id }}"
                        {{ ($filters['period_id'] ?? '') == $period->id ? 'selected' : '' }}>
                        {{ $period->getDisplayLabel() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 mt-1 d-flex align-items-end gap-1">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="la la-search"></i> بحث
            </button>
            <a href="{{ route('dashboard.support.tickets.index') }}" class="btn btn-secondary btn-sm mr-1">
                <i class="la la-times"></i>
            </a>
        </div>

    </div>
</form>
