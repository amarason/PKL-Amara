<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        // Login menggunakan login_id
        if (Auth::attempt(['login_id' => $credentials['login_id'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return $this->redirectUser(Auth::user());
        }

        return back()->withErrors([
            'login_id' => 'ID Login atau Password salah.',
        ])->onlyInput('login_id');
    }

    protected function redirectUser($user)
    {
        // Redirect berdasarkan role
        if ($user->role_id === 'ROLE_ADMIN') {
            return redirect()->intended('/admin/dashboard');
        }
        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}