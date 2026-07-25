<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Connexion extends Component
{
    public string $email = '';

    public string $password = '';

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = mb_strtolower($credentials['email']).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Trop de tentatives. Réessaie dans {$seconds} secondes.");

            return;
        }

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('email', 'Identifiants incorrects.');

            return;
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        $this->redirect(route('accueil'));
    }

    public function render()
    {
        return view('livewire.auth.connexion')->layout('layouts.app');
    }
}
