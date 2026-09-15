@extends('layouts.admin')

@section('title', 'Anggota')

@section('content')
<form method="GET" action="{{ route('admin.members.index') }}" class="flex flex-wrap gap-3 mb-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIP, atau nomor anggota..."
        class="flex-1 min-w-[240px] rounded-lg border border-outline px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary">

    <select name="status" class="rounded-lg border border-outline px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary">
        <option value="">Semua Status</option>
        @foreach ($statuses as $status)
            <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                {{ $status->label() }}
            </option>
        @endforeach
    </select>

    <button type="submit" class="bg-primary text-white rounded-lg px-4 py-2 text-sm font-semibold hover:bg-primary-container transition">
        Cari
    </button>
</form>

<div class="bg-surface rounded-xl border border-outline overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-surface-variant text-on-surface-variant text-left">
            <tr>
                <th class="px-5 py-3 font-medium">Nama</th>
                <th class="px-5 py-3 font-medium">No. Anggota</th>
                <th class="px-5 py-3 font-medium">NIP</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-outline">
            @forelse ($members as $member)
                <tr>
                    <td class="px-5 py-3 font-medium text-on-surface">{{ $member->full_name }}</td>
                    <td class="px-5 py-3 text-on-surface-variant">{{ $member->member_number ?? '—' }}</td>
                    <td class="px-5 py-3 text-on-surface-variant">{{ $member->employee_nip }}</td>
                    <td class="px-5 py-3">
                        <span @class([
                            'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                            'bg-success-container text-success' => $member->status->value === 'active',
                            'bg-warning-container text-warning' => $member->status->value === 'pending',
                            'bg-error-container text-error' => in_array($member->status->value, ['resigned', 'suspended']),
                        ])>
                            {{ $member->status->label() }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.members.show', $member) }}" class="text-secondary font-medium hover:underline">Lihat</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-8 text-center text-on-surface-variant">Tidak ada anggota yang cocok.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $members->links() }}
</div>
@endsection
