@extends('layouts.admin')

@section('title', 'Tinjau KYC — ' . $kyc->member->full_name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-surface rounded-xl border border-outline p-5">
            <h2 class="font-heading font-bold text-on-surface mb-3">Data Anggota</h2>
            <dl class="grid grid-cols-2 gap-y-2 text-sm">
                <dt class="text-on-surface-variant">Nama Lengkap</dt>
                <dd class="text-on-surface font-medium">{{ $kyc->member->full_name }}</dd>
                <dt class="text-on-surface-variant">NIP</dt>
                <dd class="text-on-surface font-medium">{{ $kyc->member->employee_nip }}</dd>
                <dt class="text-on-surface-variant">Departemen</dt>
                <dd class="text-on-surface font-medium">{{ $kyc->member->department }}</dd>
                <dt class="text-on-surface-variant">Email</dt>
                <dd class="text-on-surface font-medium">{{ $kyc->member->user->email }}</dd>
                <dt class="text-on-surface-variant">No. HP</dt>
                <dd class="text-on-surface font-medium">{{ $kyc->member->user->phone_number }}</dd>
            </dl>
        </div>

        <div class="bg-surface rounded-xl border border-outline p-5">
            <h2 class="font-heading font-bold text-on-surface mb-3">Berkas KYC</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-on-surface-variant mb-2">Foto KTP</p>
                    <img src="{{ $ktpPhotoUrl }}" alt="Foto KTP" class="rounded-lg border border-outline w-full object-cover">
                </div>
                <div>
                    <p class="text-xs text-on-surface-variant mb-2">Swafoto dengan KTP</p>
                    <img src="{{ $selfiePhotoUrl }}" alt="Swafoto dengan KTP" class="rounded-lg border border-outline w-full object-cover">
                </div>
            </div>
            <p class="text-xs text-on-surface-variant mt-3">Tautan berkas berlaku 5 menit sejak halaman ini dimuat (presigned URL).</p>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-surface rounded-xl border border-outline p-5">
            <h2 class="font-heading font-bold text-on-surface mb-3">Keputusan</h2>

            <form method="POST" action="{{ route('admin.kyc.approve', $kyc) }}" class="mb-3">
                @csrf
                <button type="submit" class="w-full bg-success text-white rounded-lg py-2.5 text-sm font-semibold hover:opacity-90 transition">
                    Setujui KYC
                </button>
            </form>

            <form method="POST" action="{{ route('admin.kyc.reject', $kyc) }}" class="space-y-2">
                @csrf
                <textarea name="reason" rows="3" required placeholder="Alasan penolakan..."
                    class="w-full rounded-lg border border-outline px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary">{{ old('reason') }}</textarea>
                <button type="submit" class="w-full bg-error text-white rounded-lg py-2.5 text-sm font-semibold hover:opacity-90 transition">
                    Tolak KYC
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
