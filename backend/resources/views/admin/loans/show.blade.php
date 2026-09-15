@extends('layouts.admin')

@section('title', 'Tinjau Pengajuan — ' . $application->application_number)

@php
    $approvedCount = $application->approvals->where('decision', \App\Enums\LoanApprovalDecision::APPROVED)->count();
    $alreadyDecided = $application->approvals->contains('approver_id', auth()->id());
    $eligible = in_array(auth()->user()->role->value, $tier->roles, true);
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-surface rounded-xl border border-outline p-5">
            <h2 class="font-heading font-bold text-on-surface mb-3">Detail Pengajuan</h2>
            <dl class="grid grid-cols-2 gap-y-2 text-sm">
                <dt class="text-on-surface-variant">Anggota</dt>
                <dd class="text-on-surface font-medium">{{ $application->member->full_name }} ({{ $application->member->employee_nip }})</dd>
                <dt class="text-on-surface-variant">Produk</dt>
                <dd class="text-on-surface font-medium">{{ $application->loanProduct->name }}</dd>
                <dt class="text-on-surface-variant">Nominal</dt>
                <dd class="text-on-surface font-medium">Rp {{ number_format((float) $application->amount, 0, ',', '.') }}</dd>
                <dt class="text-on-surface-variant">Tenor</dt>
                <dd class="text-on-surface font-medium">{{ $application->tenor_months }} bulan</dd>
                <dt class="text-on-surface-variant">Jaminan</dt>
                <dd class="text-on-surface font-medium">{{ $application->guarantee_type ?? '—' }}</dd>
                <dt class="text-on-surface-variant">Tujuan</dt>
                <dd class="text-on-surface font-medium">{{ $application->purpose }}</dd>
            </dl>
        </div>

        <div class="bg-surface rounded-xl border border-outline p-5">
            <h2 class="font-heading font-bold text-on-surface mb-3">Riwayat Persetujuan</h2>
            <p class="text-sm text-on-surface-variant mb-3">
                Jenjang nominal ini membutuhkan <strong>{{ $tier->requiredApprovals }}</strong> persetujuan dari peran:
                {{ implode(', ', $tier->roles) }}. Saat ini terkumpul <strong>{{ $approvedCount }}</strong>.
            </p>
            @forelse ($application->approvals as $approval)
                <div class="flex items-center justify-between py-2 border-t border-outline text-sm first:border-t-0">
                    <span class="text-on-surface">{{ $approval->approver->name }}</span>
                    <span class="{{ $approval->decision->value === 'approved' ? 'text-success' : 'text-error' }} font-medium">
                        {{ $approval->decision->value === 'approved' ? 'Disetujui' : 'Ditolak' }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-on-surface-variant">Belum ada keputusan.</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-surface rounded-xl border border-outline p-5">
            <h2 class="font-heading font-bold text-on-surface mb-3">Keputusan Anda</h2>

            @if ($alreadyDecided)
                <p class="text-sm text-on-surface-variant">Anda sudah memberikan keputusan untuk pengajuan ini.</p>
            @elseif (! $eligible)
                <p class="text-sm text-on-surface-variant">Peran Anda tidak berwenang memutuskan pengajuan pada jenjang nominal ini.</p>
            @else
                <form method="POST" action="{{ route('admin.loans.applications.decide', $application) }}" class="space-y-2 mb-3">
                    @csrf
                    <input type="hidden" name="decision" value="approved">
                    <button type="submit" class="w-full bg-success text-white rounded-lg py-2.5 text-sm font-semibold hover:opacity-90 transition">
                        Setujui
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.loans.applications.decide', $application) }}" class="space-y-2">
                    @csrf
                    <input type="hidden" name="decision" value="rejected">
                    <textarea name="notes" rows="3" placeholder="Alasan penolakan (opsional)..."
                        class="w-full rounded-lg border border-outline px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary">{{ old('notes') }}</textarea>
                    <button type="submit" class="w-full bg-error text-white rounded-lg py-2.5 text-sm font-semibold hover:opacity-90 transition">
                        Tolak
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
