<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function show()
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'username' => ['required', 'string', 'max:50', 'unique:users', 'regex:/^[a-zA-Z0-9_]+$/'],
        ], [
            'name.required' => 'Jméno je povinné.',
            'email.required' => 'Email je povinný.',
            'email.email' => 'Zadejte platný email.',
            'email.unique' => 'Tento email je již registrován.',
            'password.required' => 'Heslo je povinné.',
            'password.min' => 'Heslo musí mít alespoň 8 znaků.',
            'password.confirmed' => 'Hesla se neshodují.',
            'username.required' => 'Uživatelské jméno je povinné.',
            'username.unique' => 'Toto uživatelské jméno je již obsazeno.',
            'username.regex' => 'Uživatelské jméno může obsahovat pouze písmena, čísla a podtržítka.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'username' => $validated['username'],
        ]);

        Auth::login($user);

        return redirect('/');
    }
}
