<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::where('user_id', (string) Auth::id())
            ->latest()
            ->paginate(20);

        return view('activity.index', compact('logs'));
    }
}
