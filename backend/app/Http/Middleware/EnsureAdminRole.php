<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Portal Web Admin khusus Super Admin & Pengurus (Bendahara, Ketua, Pengawas) —
 * Anggota koperasi hanya boleh bertransaksi lewat aplikasi mobile (AGENTS.md §1).
 */
class EnsureAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role === UserRole::MEMBER) {
            abort(403, 'Portal ini khusus untuk Pengurus dan Super Admin.');
        }

        return $next($request);
    }
}
