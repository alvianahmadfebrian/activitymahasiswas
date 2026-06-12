<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('profile.index', compact('user'));
    }

    public function update(Request $request, SupabaseStorageService $storage)
    {
        $user = User::findOrFail((string) Auth::id());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'nim' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:120'],
            'prodi' => ['nullable', 'string', 'max:120'],
            'kelas' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $emailExists = User::where('email', $data['email'])
            ->where('_id', '!=', (string) $user->_id)
            ->exists();

        if ($emailExists) {
            return back()
                ->withErrors(['email' => 'Email sudah digunakan oleh akun lain.'])
                ->withInput();
        }

        $payload = [
            'name' => $data['name'],
            'nim' => $data['nim'] ?? null,
            'email' => $data['email'],
            'prodi' => $data['prodi'] ?? null,
            'kelas' => $data['kelas'] ?? null,
            'bio' => $data['bio'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            $upload = $storage->uploadProfilePhoto($request->file('photo'));

            $payload['photo_url'] = $upload['url'];
            $payload['photo_path'] = $upload['path'];
        }

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        User::where('_id', (string) $user->_id)->update($payload);

        ActivityLogger::log('profile_update', 'Memperbarui data profile');

        return redirect()
            ->route('profile.index')
            ->with('success', 'Profile berhasil diperbarui.');
    }
}
