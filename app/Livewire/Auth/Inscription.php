<?php

namespace App\Livewire\Auth;

use App\Models\Famille;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Inscription extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $family_code = '';

    public function register(): void
    {
        $throttleKey = 'register|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('family_code', "Trop de tentatives. Réessaie dans {$seconds} secondes.");

            return;
        }

        RateLimiter::hit($throttleKey, 60);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'family_code' => ['required', 'string', 'max:255'],
        ]);

        $family = Famille::trouverOuCreerParCode($data['family_code']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'family_id' => $family->id,
        ]);

        Auth::login($user);

        session()->regenerate();

        $this->redirect(route('accueil'));
    }

    public function render()
    {
        return view('livewire.auth.inscription')->layout('layouts.app');
    }
}
