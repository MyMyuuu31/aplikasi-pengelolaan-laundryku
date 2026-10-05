<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Batasi akses route berdasarkan role pengguna.
     * Contoh: ->middleware('role:admin,kasir')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (! in_array($user->role, $roles, true)) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki hak akses untuk halaman tersebut.');
        }

        return $next($request);
    }
}