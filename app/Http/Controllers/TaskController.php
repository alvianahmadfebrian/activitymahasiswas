<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\ActivityLogger;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::where('user_id', (string) Auth::id())
            ->latest()
            ->get();

        return view('tasks.index', compact('tasks'));
    }

    public function store(Request $request, SupabaseStorageService $storage)
    {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
            'status' => ['required', 'in:belum,proses,selesai'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        $fileUrl = null;
        $filePath = null;
        $fileName = null;

        if ($request->hasFile('file')) {
            $upload = $storage->uploadTaskFile($request->file('file'));
            $fileUrl = $upload['url'];
            $filePath = $upload['path'];
            $fileName = $upload['original_name'];
        }

        Task::create([
            'user_id' => (string) Auth::id(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'status' => $data['status'],
            'file_url' => $fileUrl,
            'file_path' => $filePath,
            'file_name' => $fileName,
        ]);

        ActivityLogger::log('task_create', 'Membuat tugas: ' . $data['title']);

        return back()->with('success', 'Tugas berhasil dibuat.');
    }

    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => ['required', 'in:belum,proses,selesai'],
        ]);

        $task = Task::where('id', $id)
            ->where('user_id', (string) Auth::id())
            ->firstOrFail();

        $task->update([
            'status' => $request->status,
        ]);

        ActivityLogger::log('task_update', 'Mengubah status tugas: ' . $task->title);

        return back()->with('success', 'Status tugas diperbarui.');
    }

    public function destroy(string $id)
    {
        $task = Task::where('id', $id)
            ->where('user_id', (string) Auth::id())
            ->firstOrFail();

        $title = $task->title;
        $task->delete();

        ActivityLogger::log('task_delete', 'Menghapus tugas: ' . $title);

        return back()->with('success', 'Tugas berhasil dihapus.');
    }
}
