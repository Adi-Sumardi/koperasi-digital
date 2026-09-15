@extends('layouts.admin')

@section('title', 'Keamanan Akun')

@section('content')
<div class="max-w-lg space-y-4">
    <div class="bg-surface rounded-xl border border-outline p-5">
        <div class="flex items-center justify-between mb-1">
            <h2 class="font-heading font-bold text-on-surface">Verifikasi Dua Langkah (2FA)</h2>
            <span @class([
                'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                'bg-success-container text-success' => $user->hasEnabledTwoFactor(),
                'bg-warning-container text-warning' => ! $user->hasEnabledTwoFactor(),
            ])>
                {{ $user->hasEnabledTwoFactor() ? 'Aktif' : 'Belum Aktif' }}
            </span>
        </div>

        @if ($user->role->value === 'superadmin')
            <p class="text-xs text-on-surface-variant mb-4">Wajib untuk akun Super Admin.</p>
        @else
            <p class="text-xs text-on-surface-variant mb-4">Disarankan untuk keamanan tambahan saat masuk ke portal.</p>
        @endif

        @if ($user->hasEnabledTwoFactor())
            <p class="text-sm text-on-surface-variant mb-4">
                Akun Anda dilindungi dengan kode verifikasi dari aplikasi authenticator setiap kali masuk.
            </p>

            <form method="POST" action="{{ route('admin.settings.security.2fa.disable') }}" class="space-y-2">
                @csrf
                <label for="current_password" class="block text-sm font-medium text-on-surface-variant">Kata Sandi Saat Ini</label>
                <input id="current_password" name="current_password" type="password" required
                    class="w-full rounded-lg border border-outline px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary">
                <button type="submit" class="bg-error text-white rounded-lg px-4 py-2 text-sm font-semibold hover:opacity-90 transition">
                    Nonaktifkan 2FA
                </button>
            </form>
        @else
            <p class="text-sm text-on-surface-variant mb-4">
                Aktifkan untuk mewajibkan kode dari aplikasi authenticator (mis. Google Authenticator) setiap kali masuk.
            </p>

            <a href="{{ route('admin.settings.security.2fa.enable') }}"
                class="inline-block bg-primary text-white rounded-lg px-4 py-2 text-sm font-semibold hover:bg-primary-container transition">
                Aktifkan 2FA
            </a>
        @endif
    </div>
</div>
@endsection
