<?php

namespace App\Http\Controllers;

use App\Models\DiscussionMessage;
use App\Models\DiscussionRoom;
use App\Models\User;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DiscussionController extends Controller
{
    private function isUserOnline($user): bool
    {
        if (!$user || empty($user->last_active_at)) {
            return false;
        }

        try {
            return Carbon::parse($user->last_active_at)->greaterThan(now()->subMinutes(5));
        } catch (\Throwable $e) {
            return false;
        }
    }

private function updateUserOnlineStatus(): void
{
    if (!Auth::check()) {
        return;
    }

    User::where('_id', (string) Auth::id())->update([
        'last_active_at' => now(),
    ]);
}

    public function index()
    {
        $this->updateUserOnlineStatus();

        $authId = (string) Auth::id();

        $rooms = DiscussionRoom::orderBy('updated_at', 'desc')->get();

        $users = User::where('_id', '!=', $authId)->get()->map(function ($user) {
            $user->is_online = $this->isUserOnline($user);
            return $user;
        });

        return view('discussions.index', compact('rooms', 'users'));
    }

    public function storeRoom(Request $request)
    {
        $this->updateUserOnlineStatus();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'course' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'member_ids' => ['nullable', 'array'],
        ]);

        $memberIds = $data['member_ids'] ?? [];
        $memberIds[] = (string) Auth::id();
        $memberIds = array_values(array_unique($memberIds));

        $room = DiscussionRoom::create([
            'user_id' => (string) Auth::id(),
            'title' => $data['title'],
            'course' => $data['course'] ?? null,
            'description' => $data['description'] ?? null,
            'type' => 'group',
            'member_ids' => $memberIds,
        ]);

        ActivityLogger::log('discussion_create', 'Membuat group diskusi: ' . $data['title']);

        return redirect()->route('discussions.show', $room->_id)
            ->with('success', 'Group diskusi berhasil dibuat.');
    }

    public function show(string $id)
    {
        $this->updateUserOnlineStatus();

        $authId = (string) Auth::id();

        $room = DiscussionRoom::findOrFail($id);

        $messages = DiscussionMessage::where('room_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $rooms = DiscussionRoom::orderBy('updated_at', 'desc')->get();

        $users = User::where('_id', '!=', $authId)->get()->map(function ($user) {
            $user->is_online = $this->isUserOnline($user);
            return $user;
        });

        return view('discussions.show', compact('room', 'messages', 'rooms', 'users'));
    }

    public function sendMessage(Request $request, string $id)
    {
        $this->updateUserOnlineStatus();

        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $room = DiscussionRoom::findOrFail($id);

        $message = DiscussionMessage::create([
            'room_id' => $id,
            'user_id' => (string) Auth::id(),
            'user_name' => Auth::user()->name,
            'message' => $data['message'],
        ]);

        $room->updated_at = now();
        $room->save();

        ActivityLogger::log('discussion_message', 'Mengirim pesan diskusi');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message_id' => (string) $message->_id,
            ]);
        }

        return back();
    }

    public function startCall(Request $request, string $id)
    {
        $this->updateUserOnlineStatus();

        $data = $request->validate([
            'type' => ['required', 'string', 'in:voice,video'],
        ]);

        $room = DiscussionRoom::findOrFail($id);
        $user = Auth::user();

        $callId = (string) Str::uuid();
        $type = $data['type'];

        $payload = [
            'id' => $callId,
            'type' => $type,
            'room_id' => (string) $room->_id,
            'room_title' => $room->title,
            'from_user_id' => (string) Auth::id(),
            'from_user_name' => $user->name,
            'started_at' => now()->toISOString(),
        ];

        $icon = $type === 'voice' ? '📞' : '🎥';
        $label = $type === 'voice' ? 'telepon suara' : 'video call';

        $message = DiscussionMessage::create([
            'room_id' => $id,
            'user_id' => (string) Auth::id(),
            'user_name' => $user->name,
            'message' => 'CALL_INVITE::' . json_encode($payload),
        ]);

        DiscussionMessage::create([
            'room_id' => $id,
            'user_id' => (string) Auth::id(),
            'user_name' => $user->name,
            'message' => 'CALL_LOG::' . $icon . ' ' . $user->name . ' memulai ' . $label . '.',
        ]);

        $room->updated_at = now();
        $room->save();

        ActivityLogger::log('discussion_call_start', 'Memulai panggilan diskusi');

        return response()->json([
            'success' => true,
            'invite_message_id' => (string) $message->_id,
            'call' => $payload,
        ]);
    }

    public function checkCall(Request $request, string $id)
    {
        $this->updateUserOnlineStatus();

        DiscussionRoom::findOrFail($id);

        $lastSeen = (string) $request->query('last_seen', '');
        $authId = (string) Auth::id();

        $messages = DiscussionMessage::where('room_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        foreach ($messages as $message) {
            $messageText = (string) ($message->message ?? '');

            if (!str_starts_with($messageText, 'CALL_INVITE::')) {
                continue;
            }

            if ((string) $message->_id === $lastSeen) {
                continue;
            }

            if ((string) $message->user_id === $authId) {
                continue;
            }

            if ($message->created_at && Carbon::parse($message->created_at)->lessThan(now()->subSeconds(45))) {
                continue;
            }

            $json = str_replace('CALL_INVITE::', '', $messageText);
            $payload = json_decode($json, true);

            if (!$payload) {
                continue;
            }

            return response()->json([
                'success' => true,
                'has_call' => true,
                'invite_message_id' => (string) $message->_id,
                'call' => $payload,
            ]);
        }

        return response()->json([
            'success' => true,
            'has_call' => false,
        ]);
    }

    public function startPrivateChat(Request $request)
    {
        $this->updateUserOnlineStatus();

        $data = $request->validate([
            'target_user_id' => ['required', 'string'],
        ]);

        $myId = (string) Auth::id();
        $targetId = (string) $data['target_user_id'];

        $ids = [$myId, $targetId];
        sort($ids);

        $privateKey = implode('_', $ids);

        $room = DiscussionRoom::where('type', 'private')
            ->where('private_key', $privateKey)
            ->first();

        if (!$room) {
            $targetUser = User::findOrFail($targetId);

            $room = DiscussionRoom::create([
                'user_id' => $myId,
                'title' => $targetUser->name,
                'course' => null,
                'description' => 'Chat pribadi',
                'type' => 'private',
                'private_key' => $privateKey,
                'member_ids' => [$myId, $targetId],
            ]);
        }

        return redirect()->route('discussions.show', $room->_id);
    }
}
