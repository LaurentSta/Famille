<?php

namespace App\Livewire\Auth;

use App\Models\Family;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $family_code = '';

    public function register(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'family_code' => ['required', 'string', 'max:255'],
        ]);

        $family = Family::findOrCreateByCode($data['family_code']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'family_id' => $family->id,
        ]);

        Auth::login($user);

        session()->regenerate();

        $this->redirect(route('home'));
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('layouts.app');
    }
}
