<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateNoticeController extends Controller
{
    public function dismiss(Request $request): RedirectResponse
    {
        $version = trim((string) $request->query('version', ''));
        if ($version !== '') {
            session(['admin_update_notice_dismissed_version' => $version]);
        }

        return back();
    }
}

