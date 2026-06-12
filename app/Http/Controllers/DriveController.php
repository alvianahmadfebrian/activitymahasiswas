<?php

namespace App\Http\Controllers;

use App\Models\DriveFile;
use App\Services\ActivityLogger;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriveController extends Controller
{
    public function index(Request $request)
    {
        $folder = $request->query('folder', 'root');

        $files = DriveFile::where('user_id', (string) Auth::id())
            ->where('folder', $folder)
            ->latest()
            ->get();

        return view('drive.index', compact('files', 'folder'));
    }

    public function upload(Request $request, SupabaseStorageService $storage)
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:20480'],
            'folder' => ['nullable', 'string'],
        ]);

        $folder = $data['folder'] ?? 'root';
        $upload = $storage->uploadDriveFile($request->file('file'), $folder);

        DriveFile::create([
            'user_id' => (string) Auth::id(),
            'folder' => $folder,
            'name' => $upload['original_name'],
            'original_name' => $upload['original_name'],
            'mime_type' => $upload['mime_type'],
            'size' => $upload['size'],
            'path' => $upload['path'],
            'url' => $upload['url'],
            'is_folder' => false,
        ]);

        ActivityLogger::log('drive_upload', 'Upload file drive: ' . $upload['original_name']);

        return back()->with('success', 'File berhasil diupload ke Supabase.');
    }

    public function createFolder(Request $request)
    {
        $data = $request->validate([
            'folder_name' => ['required', 'string', 'max:100'],
            'current_folder' => ['nullable', 'string'],
        ]);

        DriveFile::create([
            'user_id' => (string) Auth::id(),
            'folder' => $data['current_folder'] ?? 'root',
            'name' => $data['folder_name'],
            'original_name' => $data['folder_name'],
            'is_folder' => true,
        ]);

        ActivityLogger::log('drive_folder', 'Membuat folder drive: ' . $data['folder_name']);

        return back()->with('success', 'Folder berhasil dibuat.');
    }

    public function destroy(string $id)
    {
        $file = DriveFile::where('_id', $id)
            ->where('user_id', (string) Auth::id())
            ->firstOrFail();

        $name = $file->name;
        $file->delete();

        ActivityLogger::log('drive_delete', 'Menghapus file/folder drive: ' . $name);

        return back()->with('success', 'File/folder berhasil dihapus.');
    }
}
