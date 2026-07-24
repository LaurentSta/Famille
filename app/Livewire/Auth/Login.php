<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            $this->addError('email', 'Identifiants incorrects.');

            return;
        }

        session()->regenerate();

        $this->redirect(route('home'));
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.app');
    }
}
