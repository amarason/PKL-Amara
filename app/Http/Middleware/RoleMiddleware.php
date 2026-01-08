<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Cek apakah role_id user sesuai dengan parameter di route
        if (in_array($user->role_id, $roles)) {
            return $next($request);
        }

        // Jika tidak punya akses, arahkan ke dashboard masing-masing
        return $user->role_id === 'ROLE_ADMIN' 
            ? redirect('/admin/dashboard') 
            : redirect('/dashboard');
    }
}