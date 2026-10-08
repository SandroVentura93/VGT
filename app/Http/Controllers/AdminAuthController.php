<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function create(): View
    {
        return view('admin.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
        $configuredUsername = (string) config('services.admin_username');
        $configuredPassword = (string) config('services.admin_password');

        if ($configuredPassword === '' || ! hash_equals($configuredUsername, (string) $request->string('username')) || ! hash_equals($configuredPassword, (string) $request->string('password'))) {
            return back()->withErrors(['username' => 'El usuario o la contraseña no son válidos.'])->onlyInput('username');
        }

        $request->session()->regenerate();
        $request->session()->put('admin_authenticated', true);

        return redirect()->route('admin.productos.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_authenticated');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
