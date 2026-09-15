@extends('layouts.admin')

@section('title', 'Persetujuan Pinjaman')

@section('content')
<div class="bg-surface rounded-xl border border-outline overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-surface-variant text-on-surface-variant text-left">
            <tr>
                <th class="px-5 py-3 font-medium">No. Pengajuan</th>
                <th class="px-5 py-3 font-medium">Anggota</th>
                <th class="px-5 py-3 font-medium">Produk</th>
                <th class="px-5 py-3 font-medium text-right">Nominal</th>
                <th class="px-5 py-3 font-medium">Tenor</th>
                <th class="px-5 py-3 font-medium"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-outline">
            @forelse ($applications as $application)
                <tr>
                    <td class="px-5 py-3 font-medium text-on-surface">{{ $application->application_number }}</td>
                    <td class="px-5 py-3 text-on-surface-variant">{{ $application->member->full_name }}</td>
                    <td class="px-5 py-3 text-on-surface-variant">{{ $application->loanProduct->name }}</td>
                    <td class="px-5 py-3 text-right font-data text-on-surface">Rp {{ number_format((float) $application->amount, 0, ',', '.') }}</td>
                    <td class="px-5 py-3 text-on-surface-variant">{{ $application->tenor_months }} bulan</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.loans.applications.show', $application) }}" class="text-secondary font-medium hover:underline">Tinjau</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-on-surface-variant">Tidak ada pengajuan pinjaman yang menunggu keputusan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $applications->links() }}
</div>
@endsection
