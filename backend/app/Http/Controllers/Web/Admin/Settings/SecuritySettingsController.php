<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SecuritySettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.settings.security', ['user' => $request->user()]);
    }
}
