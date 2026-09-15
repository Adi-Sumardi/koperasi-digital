@extends('layouts.admin')

@section('title', 'Verifikasi KYC')

@section('content')
<div class="bg-surface rounded-xl border border-outline overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-surface-variant text-on-surface-variant text-left">
            <tr>
                <th class="px-5 py-3 font-medium">Nama Anggota</th>
                <th class="px-5 py-3 font-medium">NIP</th>
                <th class="px-5 py-3 font-medium">Diajukan</th>
                <th class="px-5 py-3 font-medium"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-outline">
            @forelse ($submissions as $submission)
                <tr>
                    <td class="px-5 py-3 font-medium text-on-surface">{{ $submission->member->full_name }}</td>
                    <td class="px-5 py-3 text-on-surface-variant">{{ $submission->member->employee_nip }}</td>
                    <td class="px-5 py-3 text-on-surface-variant">{{ $submission->created_at->translatedFormat('d M Y, H:i') }} WIB</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.kyc.show', $submission) }}" class="text-secondary font-medium hover:underline">Tinjau</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-5 py-8 text-center text-on-surface-variant">Tidak ada pengajuan KYC yang menunggu verifikasi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $submissions->links() }}
</div>
@endsection
