<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DiscussionRoom;
use App\Models\DriveFile;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = (string) Auth::id();

        return view('dashboard', [
            'totalTasks' => Task::where('user_id', $userId)->count(),
            'doneTasks' => Task::where('user_id', $userId)->where('status', 'selesai')->count(),
            'totalFiles' => DriveFile::where('user_id', $userId)->where('is_folder', false)->count(),
            'totalRooms' => DiscussionRoom::all()
                ->filter(fn ($room) => in_array($userId, array_map('strval', $room->member_ids ?? []), true))
                ->count(),
            'tasks' => Task::where('user_id', $userId)->latest()->limit(5)->get(),
            'files' => DriveFile::where('user_id', $userId)->latest()->limit(5)->get(),
            'logs' => ActivityLog::where('user_id', $userId)->latest()->limit(10)->get(),
        ]);
    }
}
