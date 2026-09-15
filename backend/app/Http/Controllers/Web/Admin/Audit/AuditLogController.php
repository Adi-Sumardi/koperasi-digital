<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Audit;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->with('user')
            ->when($request->filled('event'), fn ($query) => $query->where('event', 'ilike', '%'.$request->string('event').'%'))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->string('user_id')))
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit-logs.index', ['logs' => $logs]);
    }
}
