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
 *
 * Terima daftar role opsional untuk membatasi lebih lanjut, mis. `admin.role:auditor,superadmin`.
 */
class EnsureAdminRole
{
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $role = $request->user()?->role;

        if ($role === UserRole::MEMBER) {
            abort(403, 'Portal ini khusus untuk Pengurus dan Super Admin.');
        }

        if ($allowedRoles !== [] && ! in_array($role?->value, $allowedRoles, true)) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
