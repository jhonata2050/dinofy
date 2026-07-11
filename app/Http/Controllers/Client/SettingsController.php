<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::guard('tenant')->user();
        $tenant = $user->tenant;

        return view('client.settings.index', compact('user', 'tenant'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('tenant')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:tenant_users,email,{$user->id}",
        ]);

        $user->update($validated);

        return back()->with('success', 'Perfil atualizado.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::guard('tenant')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Senha atual incorreta.']);
        }

        $user->update(['password' => bcrypt($request->password)]);

        return back()->with('success', 'Senha alterada com sucesso.');
    }
}
