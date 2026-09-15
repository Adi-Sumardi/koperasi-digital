<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aktifkan 2FA — Portal Pengurus Koperasi Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-primary-container min-h-screen flex items-center justify-center p-6 font-data">
    <div class="w-full max-w-md bg-surface rounded-2xl shadow-xl p-8">
        <p class="font-heading font-bold text-2xl text-on-surface">Aktifkan Verifikasi Dua Langkah</p>
        <p class="text-sm text-on-surface-variant mt-1 mb-6">
            Wajib untuk akun Super Admin. Pindai kode QR berikut dengan aplikasi authenticator
            (Google Authenticator, Authy, dll.), lalu masukkan kode 6 digit untuk mengonfirmasi.
        </p>

        @if ($errors->any())
            <div class="rounded-lg bg-error-container text-error px-4 py-3 text-sm font-medium mb-4">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="flex justify-center bg-surface-variant rounded-lg p-4 mb-4">
            {!! $qrSvg !!}
        </div>

        <p class="text-xs text-on-surface-variant mb-1">Tidak bisa memindai? Masukkan kunci ini secara manual:</p>
        <p class="font-data text-sm text-on-surface bg-surface-variant rounded-lg px-3 py-2 mb-4 break-all">{{ $secret }}</p>

        <form method="POST" action="{{ route('admin.2fa.setup.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="code" class="block text-sm font-medium text-on-surface-variant mb-1">Kode Verifikasi</label>
                <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" required autofocus
                    class="w-full rounded-lg border border-outline px-3 py-2 text-sm tracking-widest focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>
            <button type="submit"
                class="w-full bg-primary text-white rounded-lg py-2.5 text-sm font-semibold hover:bg-primary-container transition">
                Konfirmasi & Aktifkan
            </button>
        </form>
    </div>
</body>
</html>
