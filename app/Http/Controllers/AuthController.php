<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginPage()
    {
        return view('auth.login');
    }

    public function registerPage()
    {
        return view('auth.register');
    }

    public function register(Request $request, SupabaseStorageService $storage)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'nim' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6', 'confirmed'],
            'prodi' => ['nullable', 'string'],
            'kelas' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $photoUrl = null;
        $photoPath = null;

        if ($request->hasFile('photo')) {
            $upload = $storage->uploadProfilePhoto($request->file('photo'));
            $photoUrl = $upload['url'];
            $photoPath = $upload['path'];
        }

        $user = User::create([
            'name' => $data['name'],
            'nim' => $data['nim'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'prodi' => $data['prodi'] ?? null,
            'kelas' => $data['kelas'] ?? null,
            'photo_url' => $photoUrl,
            'photo_path' => $photoPath,
        ]);

        Auth::login($user);

        ActivityLogger::log('register', 'Mahasiswa membuat akun baru');

        return redirect()->route('dashboard');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();

        ActivityLogger::log('login', 'Mahasiswa login');

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        ActivityLogger::log('logout', 'Mahasiswa logout');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
