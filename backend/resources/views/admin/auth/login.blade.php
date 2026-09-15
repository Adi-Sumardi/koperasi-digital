<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Portal Pengurus Koperasi Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-primary-container min-h-screen flex items-center justify-center p-6 font-data">
    <div class="w-full max-w-sm bg-surface rounded-2xl shadow-xl p-8">
        <p class="font-heading font-bold text-2xl text-on-surface">Koperasi Digital</p>
        <p class="text-sm text-on-surface-variant mt-1 mb-6">Portal Pengurus & Super Admin</p>

        @if ($errors->any())
            <div class="rounded-lg bg-error-container text-error px-4 py-3 text-sm font-medium mb-4">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-on-surface-variant mb-1">Email</label>
                <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}"
                    class="w-full rounded-lg border border-outline px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-on-surface-variant mb-1">Kata Sandi</label>
                <input id="password" name="password" type="password" required
                    class="w-full rounded-lg border border-outline px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>
            <label class="flex items-center gap-2 text-sm text-on-surface-variant">
                <input type="checkbox" name="remember" value="1" class="rounded border-outline">
                Ingat saya
            </label>
            <button type="submit"
                class="w-full bg-primary text-white rounded-lg py-2.5 text-sm font-semibold hover:bg-primary-container transition">
                Masuk
            </button>
        </form>
    </div>
</body>
</html>
