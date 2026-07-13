{{--
  Partial: admin action bar on ticket detail.
  Variables: $ticket
--}}
<div class="card mb-2">
    <div class="card-body py-2 d-flex flex-wrap align-items-center" style="gap:8px;">

        {{-- Priority Change --}}
        @if(! $ticket->isClosedPermanently())
            <form method="POST" action="{{ route('dashboard.support.tickets.priority', $ticket) }}"
                  class="d-inline-flex align-items-center" style="gap:4px;">
                @csrf @method('PATCH')
                <select name="priority" class="form-control form-control-sm" style="min-width:120px;">
                    @foreach(\App\Enums\TicketPriority::cases() as $pr)
                        <option value="{{ $pr->value }}" {{ $ticket->priority === $pr ? 'selected' : '' }}>
                            {{ $pr->label() }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="la la-flag"></i> تحديث الأولوية
                </button>
            </form>
        @endif

        {{-- Reopen (super_admin only, on permanently closed tickets) --}}
        @if(auth()->user()->isSuperAdmin() && $ticket->isClosedPermanently())
            <form method="POST" action="{{ route('dashboard.support.tickets.close', $ticket) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-sm btn-warning"
                        onclick="return confirm('إعادة فتح هذه التذكرة؟')">
                    <i class="la la-undo"></i> إعادة فتح
                </button>
            </form>
        @endif

    </div>
</div>
