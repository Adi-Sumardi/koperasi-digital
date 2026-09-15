@extends('layouts.admin')

@section('title', $member->full_name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-surface rounded-xl border border-outline p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-heading font-bold text-on-surface">Data Anggota</h2>
                <span @class([
                    'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                    'bg-success-container text-success' => $member->status->value === 'active',
                    'bg-warning-container text-warning' => $member->status->value === 'pending',
                    'bg-error-container text-error' => in_array($member->status->value, ['resigned', 'suspended']),
                ])>
                    {{ $member->status->label() }}
                </span>
            </div>
            <dl class="grid grid-cols-2 gap-y-2 text-sm">
                <dt class="text-on-surface-variant">Nama Lengkap</dt>
                <dd class="text-on-surface font-medium">{{ $member->full_name }}</dd>
                <dt class="text-on-surface-variant">Nomor Anggota</dt>
                <dd class="text-on-surface font-medium">{{ $member->member_number ?? '—' }}</dd>
                <dt class="text-on-surface-variant">NIK</dt>
                <dd class="text-on-surface font-medium font-data">{{ $member->maskedNik() }}</dd>
                <dt class="text-on-surface-variant">NIP</dt>
                <dd class="text-on-surface font-medium">{{ $member->employee_nip }}</dd>
                <dt class="text-on-surface-variant">Departemen</dt>
                <dd class="text-on-surface font-medium">{{ $member->department }}</dd>
                <dt class="text-on-surface-variant">Gaji Pokok</dt>
                <dd class="text-on-surface font-medium">Rp {{ number_format((float) $member->monthly_salary, 0, ',', '.') }}</dd>
                <dt class="text-on-surface-variant">Email</dt>
                <dd class="text-on-surface font-medium">{{ $member->user->email }}</dd>
                <dt class="text-on-surface-variant">No. HP</dt>
                <dd class="text-on-surface font-medium">{{ $member->user->phone_number }}</dd>
                <dt class="text-on-surface-variant">Bergabung</dt>
                <dd class="text-on-surface font-medium">{{ $member->joined_at?->translatedFormat('d M Y') ?? '—' }}</dd>
            </dl>
        </div>

        <div class="bg-surface rounded-xl border border-outline p-5">
            <h2 class="font-heading font-bold text-on-surface mb-3">Simpanan</h2>
            <div class="grid grid-cols-3 gap-3">
                @foreach ($member->savingsAccounts as $account)
                    <div class="rounded-lg border border-outline p-3">
                        <p class="text-xs text-on-surface-variant capitalize">{{ $account->type->label() }}</p>
                        <p class="font-data font-semibold text-on-surface mt-1">Rp {{ number_format((float) $account->balance, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-surface rounded-xl border border-outline p-5">
            <h2 class="font-heading font-bold text-on-surface mb-3">Riwayat Pinjaman</h2>
            @forelse ($member->loans as $loan)
                <div class="flex items-center justify-between py-2 border-t border-outline text-sm first:border-t-0">
                    <div>
                        <p class="text-on-surface font-medium">{{ $loan->loan_number }}</p>
                        <p class="text-on-surface-variant text-xs">Rp {{ number_format((float) $loan->principal_amount, 0, ',', '.') }} · {{ $loan->tenor_months }} bulan</p>
                    </div>
                    <div class="text-right">
                        <p class="text-on-surface font-medium">Sisa Rp {{ number_format((float) $loan->outstanding_balance, 0, ',', '.') }}</p>
                        <p class="text-xs text-on-surface-variant capitalize">{{ $loan->status->value }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-on-surface-variant">Belum ada riwayat pinjaman.</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-surface rounded-xl border border-outline p-5">
            <h2 class="font-heading font-bold text-on-surface mb-3">Status KYC</h2>
            @if ($member->kyc)
                <p class="text-sm text-on-surface-variant mb-1">Status: <span class="text-on-surface font-medium capitalize">{{ $member->kyc->status->value }}</span></p>
                @if ($member->kyc->verifier)
                    <p class="text-xs text-on-surface-variant">Diverifikasi oleh {{ $member->kyc->verifier->name }}</p>
                @endif
                @if ($member->kyc->status->value === 'pending')
                    <a href="{{ route('admin.kyc.show', $member->kyc) }}" class="inline-block mt-2 text-secondary text-sm font-medium hover:underline">Tinjau KYC →</a>
                @endif
            @else
                <p class="text-sm text-on-surface-variant">Anggota belum mengunggah berkas KYC.</p>
            @endif
        </div>
    </div>
</div>
@endsection
