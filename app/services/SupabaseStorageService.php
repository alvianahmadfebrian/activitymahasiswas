<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SupabaseStorageService
{
    public function uploadDriveFile(UploadedFile $file, string $folder = 'root'): array
    {
        $bucket = env('SUPABASE_BUCKET_DRIVE', 'drive-files');

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = 'drive/' . trim($folder, '/') . '/' . date('Y/m') . '/' . $filename;

        Storage::disk('supabase_drive')->put($path, file_get_contents($file->getRealPath()));

        return [
            'path' => $path,
            'url' => $this->publicUrl($bucket, $path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    public function uploadTaskFile(UploadedFile $file): array
    {
        $bucket = env('SUPABASE_BUCKET_TASK', 'task-files');

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = 'tasks/' . date('Y/m') . '/' . $filename;

        Storage::disk('supabase_task')->put($path, file_get_contents($file->getRealPath()));

        return [
            'path' => $path,
            'url' => $this->publicUrl($bucket, $path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    public function uploadProfilePhoto(UploadedFile $file): array
    {
        $bucket = env('SUPABASE_BUCKET_PROFILE', 'profile-photos');

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = 'profiles/' . date('Y/m') . '/' . $filename;

        Storage::disk('supabase_profile')->put($path, file_get_contents($file->getRealPath()));

        return [
            'path' => $path,
            'url' => $this->publicUrl($bucket, $path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    private function publicUrl(string $bucket, string $path): string
    {
        $baseUrl = rtrim(env('SUPABASE_URL'), '/');

        return "{$baseUrl}/storage/v1/object/public/{$bucket}/{$path}";
    }
}
