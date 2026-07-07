@php
    $history = $vehicle->vehicleAssignments->where('is_active', false)->sortByDesc('assigned_at');
@endphp

@if ($history->isEmpty())
    <div class="text-center text-muted py-4">
        <i class="la la-history font-large-2"></i>
        <p class="mt-2">لا يوجد سجل سائقين سابق</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>المندوب</th>
                    <th>الكود</th>
                    <th>تاريخ البداية</th>
                    <th>تاريخ النهاية</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($history as $assignment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if ($assignment->delegate)
                                <a href="{{ route('dashboard.delegates.show', $assignment->delegate) }}">
                                    {{ $assignment->delegate->name }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $assignment->delegate?->delegate_code ?? '—' }}</td>
                        <td>{{ $assignment->assigned_at?->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $assignment->returned_at?->format('Y-m-d') ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
