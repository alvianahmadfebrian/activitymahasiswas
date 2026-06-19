<?php

namespace App\Http\Controllers;

use App\Models\DiscussionMessage;
use App\Models\DiscussionRoom;
use App\Models\User;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

        User::where('id', (string) Auth::id())->update([
            'last_active_at' => now(),
        ]);
    }

    /**
     * Pastikan user yang sedang login adalah anggota room.
     */
    private function ensureMember(DiscussionRoom $room): void
    {
        $authId = (string) Auth::id();

        $memberIds = array_map('strval', $room->member_ids ?? []);

        if (!in_array($authId, $memberIds, true)) {
            session()->flash('error', 'Anda bukan anggota group ini.');
            redirect()->route('discussions.index')->send();
            exit;
        }
    }

    /**
     * Ubah teks pesan mentah (termasuk CALL_LOG/CALL_INVITE) jadi preview singkat untuk list room.
     */
    private function previewMessage(?DiscussionMessage $message, string $authId): string
    {
        if (!$message) {
            return 'Belum ada pesan.';
        }

        $text = (string) ($message->message ?? '');
        $prefix = (string) $message->user_id === $authId ? 'Kamu: ' : '';

        if (str_starts_with($text, 'CALL_INVITE::')) {
            $payload = json_decode(str_replace('CALL_INVITE::', '', $text), true);
            $type = $payload['type'] ?? 'video';
            return ($type === 'voice' ? '📞 Telepon suara' : '🎥 Video call');
        }

        if (str_starts_with($text, 'CALL_LOG::')) {
            return str_replace('CALL_LOG::', '', $text);
        }

        $clean = trim($text);

        if (mb_strlen($clean) > 42) {
            $clean = mb_substr($clean, 0, 42) . '…';
        }

        return $prefix . $clean;
    }

    public function index()
    {
        $this->updateUserOnlineStatus();

        $authId = (string) Auth::id();

        $rooms = DiscussionRoom::orderBy('updated_at', 'desc')->get()
            ->filter(fn ($room) => in_array($authId, array_map('strval', $room->member_ids ?? []), true))
            ->values();

        $roomIds = $rooms->pluck('id')->map(fn ($id) => (string) $id)->all();

        $lastMessages = DiscussionMessage::whereIn('room_id', $roomIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn ($m) => (string) $m->room_id)
            ->map(fn ($group) => $group->first());

        $rooms = $rooms->map(function ($room) use ($lastMessages, $authId) {
            $last = $lastMessages->get((string) $room->id);

            $room->preview_text = $this->previewMessage($last, $authId);
            $room->preview_time = $last && $last->created_at
                ? $last->created_at->timezone('Asia/Jakarta')->format('H:i')
                : ($room->created_at ? $room->created_at->timezone('Asia/Jakarta')->format('H:i') : '');

            return $room;
        });

        $users = User::where('id', '!=', $authId)->get()->map(function ($user) {
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
        $memberIds = array_values(array_unique(array_map('strval', $memberIds)));

        $room = DiscussionRoom::create([
            'user_id' => (string) Auth::id(),
            'title' => $data['title'],
            'course' => $data['course'] ?? null,
            'description' => $data['description'] ?? null,
            'type' => 'group',
            'member_ids' => $memberIds,
        ]);

        ActivityLogger::log('discussion_create', 'Membuat group diskusi: ' . $data['title']);

        return redirect()->route('discussions.show', $room->id)
            ->with('success', 'Group diskusi berhasil dibuat.');
    }

    public function show(string $id)
    {
        $this->updateUserOnlineStatus();

        $authId = (string) Auth::id();

        $room = DiscussionRoom::findOrFail($id);

        $this->ensureMember($room);

        $messages = DiscussionMessage::where('room_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $rooms = DiscussionRoom::orderBy('updated_at', 'desc')->get()
            ->filter(fn ($r) => in_array($authId, array_map('strval', $r->member_ids ?? []), true))
            ->values();

        $users = User::where('id', '!=', $authId)->get()->map(function ($user) {
            $user->is_online = $this->isUserOnline($user);
            return $user;
        });

        $otherUser = null;

        if (($room->type ?? 'group') === 'private') {
            $memberIds = array_map('strval', $room->member_ids ?? []);
            $otherId = collect($memberIds)->first(fn ($m) => $m !== $authId);

            if ($otherId) {
                $otherUser = User::find($otherId);

                if ($otherUser) {
                    $otherUser->is_online = $this->isUserOnline($otherUser);
                }
            }
        }

        return view('discussions.show', compact('room', 'messages', 'rooms', 'users', 'otherUser'));
    }

    public function sendMessage(Request $request, string $id)
    {
        $this->updateUserOnlineStatus();

        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $room = DiscussionRoom::findOrFail($id);

        $this->ensureMember($room);

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
                'message' => [
                    'id' => (string) $message->id,
                    'user_id' => (string) $message->user_id,
                    'user_name' => $message->user_name,
                    'message' => $message->message,
                    'is_me' => true,
                    'created_at' => optional($message->created_at)->toISOString(),
                    'time' => optional($message->created_at)->timezone('Asia/Jakarta')->format('H:i'),
                ],
            ]);
        }

        return back();
    }

    /**
     * Polling pesan baru (dipanggil berkala dari JS, mirip "live update" WA).
     */
    public function pollMessages(Request $request, string $id)
    {
        $room = DiscussionRoom::findOrFail($id);

        $this->ensureMember($room);

        $authId = (string) Auth::id();
        $after = $request->query('after');

        $query = DiscussionMessage::where('room_id', $id)->orderBy('created_at', 'asc');

        if ($after) {
            try {
                $afterDate = Carbon::parse($after);
                $query->where('created_at', '>', $afterDate);
            } catch (\Throwable $e) {
                $query->whereRaw('1 = 0');
            }
        } else {
            $query->whereRaw('1 = 0');
        }

        $messages = $query->get();

        $this->updateUserOnlineStatus();

        return response()->json([
            'success' => true,
            'messages' => $messages->map(function ($m) use ($authId) {
                return [
                    'id' => (string) $m->id,
                    'user_id' => (string) $m->user_id,
                    'user_name' => $m->user_name,
                    'message' => $m->message,
                    'is_me' => (string) $m->user_id === $authId,
                    'created_at' => optional($m->created_at)->toISOString(),
                    'time' => optional($m->created_at)->timezone('Asia/Jakarta')->format('H:i'),
                ];
            }),
        ]);
    }

    /**
     * Tandai user sedang mengetik di room ini (dipanggil tiap kali user mengetik).
     */
    public function typing(Request $request, string $id)
    {
        $room = DiscussionRoom::findOrFail($id);
        $this->ensureMember($room);

        $authId = (string) Auth::id();
        $userName = Auth::user()->name;

        $key = "discussion_typing:{$id}";
        $typing = Cache::get($key, []);

        // simpan { user_id => [name, expires_at_timestamp] }, auto-expire 4 detik
        $typing[$authId] = [
            'name' => $userName,
            'expires' => now()->addSeconds(4)->timestamp,
        ];

        Cache::put($key, $typing, 10);

        return response()->json(['success' => true]);
    }

    /**
     * Cek siapa saja yang sedang mengetik di room ini (selain diri sendiri).
     */
    public function checkTyping(Request $request, string $id)
    {
        $room = DiscussionRoom::findOrFail($id);
        $this->ensureMember($room);

        $authId = (string) Auth::id();
        $key = "discussion_typing:{$id}";
        $typing = Cache::get($key, []);

        $now = now()->timestamp;

        $names = collect($typing)
            ->filter(fn ($entry, $uid) => $uid !== $authId && ($entry['expires'] ?? 0) > $now)
            ->pluck('name')
            ->values();

        return response()->json([
            'success' => true,
            'typing_users' => $names,
        ]);
    }

    public function startCall(Request $request, string $id)
    {
        $this->updateUserOnlineStatus();

        $data = $request->validate([
            'type' => ['required', 'string', 'in:voice,video'],
        ]);

        $room = DiscussionRoom::findOrFail($id);

        $this->ensureMember($room);

        $user = Auth::user();

        $callId = (string) Str::uuid();
        $type = $data['type'];

        $payload = [
            'id' => $callId,
            'type' => $type,
            'room_id' => (string) $room->id,
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
            'invite_message_id' => (string) $message->id,
            'call' => $payload,
        ]);
    }

    public function checkCall(Request $request, string $id)
    {
        $this->updateUserOnlineStatus();

        $room = DiscussionRoom::findOrFail($id);

        $this->ensureMember($room);

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

            if ((string) $message->id === $lastSeen) {
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
                'invite_message_id' => (string) $message->id,
                'call' => $payload,
            ]);
        }

        return response()->json([
            'success' => true,
            'has_call' => false,
        ]);
    }

    public function leaveGroup(string $id)
    {
        $this->updateUserOnlineStatus();

        $room = DiscussionRoom::findOrFail($id);

        $this->ensureMember($room);

        if ($room->type === 'private') {
            abort(403, 'Tidak bisa keluar dari chat pribadi.');
        }

        $memberIds = $room->member_ids ?? [];

        $memberIds = array_values(array_filter(
            $memberIds,
            fn ($member) => (string) $member !== (string) Auth::id()
        ));

        if ((string) $room->user_id === (string) Auth::id() && count($memberIds) > 0) {
            $room->user_id = (string) $memberIds[0];
        }

        $room->member_ids = $memberIds;

        if (count($memberIds) === 0) {
            DiscussionMessage::where('room_id', $room->id)->delete();
            $room->delete();
        } else {
            $room->save();
        }

        return redirect()
            ->route('discussions.index')
            ->with('success', 'Berhasil keluar dari group.');
    }

    public function kickMember(string $id, string $userId)
    {
        $this->updateUserOnlineStatus();

        $room = DiscussionRoom::findOrFail($id);

        if ((string) $room->user_id !== (string) Auth::id()) {
            abort(403);
        }

        if ((string) $userId === (string) Auth::id()) {
            abort(403, 'Tidak bisa mengeluarkan diri sendiri. Gunakan keluar group.');
        }

        $memberIds = $room->member_ids ?? [];

        $memberIds = array_values(array_filter(
            $memberIds,
            fn ($member) => (string) $member !== (string) $userId
        ));

        $room->member_ids = $memberIds;
        $room->save();

        return back()->with('success', 'Member berhasil dikeluarkan.');
    }

    public function deleteGroup(string $id)
    {
        $this->updateUserOnlineStatus();

        $room = DiscussionRoom::findOrFail($id);

        if ((string) $room->user_id !== (string) Auth::id()) {
            abort(403);
        }

        DiscussionMessage::where('room_id', $room->id)->delete();

        $room->delete();

        return redirect()
            ->route('discussions.index')
            ->with('success', 'Group berhasil dihapus.');
    }

    public function startPrivateChat(Request $request)
    {
        $this->updateUserOnlineStatus();

        $data = $request->validate([
            'target_user_id' => ['required', 'string'],
        ]);

        $myId = (string) Auth::id();
        $targetId = (string) $data['target_user_id'];

        if ($targetId === $myId) {
            abort(403, 'Tidak bisa membuat chat pribadi dengan diri sendiri.');
        }

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

        return redirect()->route('discussions.show', $room->id);
    }
}
