<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Portal Pengurus') — Koperasi Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-canvas text-on-surface font-data antialiased">
    <div class="min-h-screen flex">
        <aside class="w-64 shrink-0 bg-primary-container text-white flex flex-col">
            <div class="px-6 py-5 border-b border-white/10">
                <p class="font-heading font-bold text-lg leading-tight">Koperasi Digital</p>
                <p class="text-xs text-white/70 mt-0.5">Portal Pengurus</p>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.dashboard') ? 'bg-white/15 font-semibold' : '' }}">Dasbor</a>
                <a href="{{ route('admin.members.index') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.members.*') ? 'bg-white/15 font-semibold' : '' }}">Anggota</a>
                <a href="{{ route('admin.kyc.index') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.kyc.*') ? 'bg-white/15 font-semibold' : '' }}">Verifikasi KYC</a>
                <a href="{{ route('admin.loans.applications.index') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.loans.*') ? 'bg-white/15 font-semibold' : '' }}">Persetujuan Pinjaman</a>
                @if (in_array(auth()->user()?->role?->value, ['auditor', 'superadmin'], true))
                    <a href="{{ route('admin.audit-logs.index') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.audit-logs.*') ? 'bg-white/15 font-semibold' : '' }}">Log Audit</a>
                @endif
            </nav>
            <div class="px-4 py-4 border-t border-white/10">
                <p class="text-xs text-white/70 truncate">{{ auth()->user()?->name }}</p>
                <p class="text-[11px] text-white/50 uppercase tracking-wide">{{ auth()->user()?->role?->value }}</p>
                <a href="{{ route('admin.settings.security.index') }}" class="block mt-2 text-xs {{ request()->routeIs('admin.settings.*') ? 'text-white font-semibold' : 'text-white/80 hover:text-white' }}">Keamanan Akun</a>
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="text-xs text-white/80 hover:text-white underline">Keluar</button>
                </form>
            </div>
        </aside>

        <main class="flex-1 min-w-0">
            <header class="bg-surface border-b border-outline px-8 py-5">
                <h1 class="font-heading font-bold text-xl text-on-surface">@yield('title', 'Dasbor')</h1>
            </header>

            <div class="px-8 py-6 space-y-4">
                @if (session('status'))
                    <div class="rounded-lg bg-success-container text-success px-4 py-3 text-sm font-medium">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-lg bg-error-container text-error px-4 py-3 text-sm font-medium space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
