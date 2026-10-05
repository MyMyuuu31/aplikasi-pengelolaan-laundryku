<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function formLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['username']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->with('error', "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.");
        }

        $user = \App\Models\User::where('username', $credentials['username'])->first();

        if (! $user || ! Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            ActivityLogger::log('LOGIN', "Gagal login untuk username {$credentials['username']}", 'auth');

            throw ValidationException::withMessages([
                'username' => 'Username atau password salah.',
            ]);
        }

        if (! $user->is_active) {
            Auth::logout();

            return back()->with('error', 'Akun Anda dinonaktifkan. Silakan hubungi administrator.');
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        ActivityLogger::log('LOGIN', "User {$user->nama} ({$user->roleLabel()}) berhasil login", 'auth');

        return redirect()->intended(route('dashboard'))
            ->with('success', "Selamat datang kembali, {$user->nama}!");
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            ActivityLogger::log('LOGOUT', "User {$user->nama} ({$user->roleLabel()}) logout", 'auth');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil keluar dari aplikasi.');
    }
}