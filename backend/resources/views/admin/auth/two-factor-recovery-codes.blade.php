<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kode Pemulihan 2FA — Portal Pengurus Koperasi Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-primary-container min-h-screen flex items-center justify-center p-6 font-data">
    <div class="w-full max-w-md bg-surface rounded-2xl shadow-xl p-8">
        <p class="font-heading font-bold text-2xl text-on-surface">2FA Aktif</p>
        <p class="text-sm text-on-surface-variant mt-1 mb-4">
            Simpan 8 kode pemulihan berikut di tempat aman. Setiap kode hanya bisa dipakai
            <strong>satu kali</strong> untuk masuk apabila Anda kehilangan akses ke aplikasi authenticator.
            Kode ini <strong>tidak akan ditampilkan lagi</strong>.
        </p>

        <div class="grid grid-cols-2 gap-2 bg-surface-variant rounded-lg p-4 mb-6 font-data text-sm text-on-surface">
            @foreach ($recoveryCodes as $code)
                <div>{{ $code }}</div>
            @endforeach
        </div>

        <a href="{{ $continueUrl ?? route('admin.dashboard') }}"
            class="block text-center w-full bg-primary text-white rounded-lg py-2.5 text-sm font-semibold hover:bg-primary-container transition">
            {{ $continueLabel ?? 'Sudah Disimpan, Lanjut ke Dasbor' }}
        </a>
    </div>
</body>
</html>
