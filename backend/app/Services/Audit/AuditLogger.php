<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Jejak audit mutlak untuk aksi finansial & administratif (rules/security.md §3.1).
 */
class AuditLogger
{
    public function log(string $event, ?User $actor, ?Model $subject = null, array $old = [], array $new = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => $actor?->id,
            'event' => $event,
            'auditable_type' => $subject?->getMorphClass(),
            'auditable_id' => $subject?->getKey(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $old === [] ? null : $old,
            'new_values' => $new === [] ? null : $new,
        ]);
    }
}
