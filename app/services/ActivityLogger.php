<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(string $type, string $description, array $metadata = []): void
    {
        if (!Auth::check()) {
            return;
        }

        ActivityLog::create([
            'user_id' => (string) Auth::id(),
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
