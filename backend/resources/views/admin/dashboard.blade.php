@extends('layouts.admin')

@section('title', 'Dasbor')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <a href="{{ route('admin.kyc.index') }}" class="block bg-surface rounded-xl border border-outline p-5 hover:shadow-md transition">
        <p class="text-sm text-on-surface-variant">Verifikasi KYC Menunggu</p>
        <p class="font-heading font-bold text-3xl text-on-surface mt-2">{{ $pendingKycCount }}</p>
    </a>

    <a href="{{ route('admin.loans.applications.index') }}" class="block bg-surface rounded-xl border border-outline p-5 hover:shadow-md transition">
        <p class="text-sm text-on-surface-variant">Pengajuan Pinjaman Menunggu</p>
        <p class="font-heading font-bold text-3xl text-on-surface mt-2">{{ $pendingLoanCount }}</p>
    </a>

    <a href="{{ route('admin.loans.applications.index') }}" class="block bg-surface rounded-xl border border-outline p-5 hover:shadow-md transition">
        <p class="text-sm text-on-surface-variant">Butuh Keputusan Anda</p>
        <p class="font-heading font-bold text-3xl text-warning mt-2">{{ $undecidedByMeCount }}</p>
    </a>
</div>
@endsection
