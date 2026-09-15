@extends('layouts.admin')

@section('title', 'Log Audit')

@section('content')
<form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex flex-wrap gap-3 mb-4">
    <input type="text" name="event" value="{{ request('event') }}" placeholder="Cari jenis kejadian (mis. loan.disbursed)..."
        class="flex-1 min-w-[240px] rounded-lg border border-outline px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary">
    <button type="submit" class="bg-primary text-white rounded-lg px-4 py-2 text-sm font-semibold hover:bg-primary-container transition">
        Cari
    </button>
</form>

<div class="bg-surface rounded-xl border border-outline overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-surface-variant text-on-surface-variant text-left">
            <tr>
                <th class="px-5 py-3 font-medium">Waktu</th>
                <th class="px-5 py-3 font-medium">Kejadian</th>
                <th class="px-5 py-3 font-medium">Aktor</th>
                <th class="px-5 py-3 font-medium">IP</th>
                <th class="px-5 py-3 font-medium">Detail</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-outline">
            @forelse ($logs as $log)
                <tr>
                    <td class="px-5 py-3 text-on-surface-variant whitespace-nowrap">{{ $log->created_at->translatedFormat('d M Y, H:i') }} WIB</td>
                    <td class="px-5 py-3 font-medium text-on-surface font-data">{{ $log->event }}</td>
                    <td class="px-5 py-3 text-on-surface-variant">{{ $log->user?->name ?? 'Sistem' }}</td>
                    <td class="px-5 py-3 text-on-surface-variant font-data">{{ $log->ip_address ?? '—' }}</td>
                    <td class="px-5 py-3 text-on-surface-variant">
                        @if ($log->new_values)
                            <code class="text-xs">{{ json_encode($log->new_values, JSON_UNESCAPED_SLASHES) }}</code>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-8 text-center text-on-surface-variant">Belum ada entri log audit.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $logs->links() }}
</div>
@endsection
