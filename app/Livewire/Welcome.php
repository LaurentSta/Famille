<?php

namespace App\Livewire;

use Livewire\Component;

class Welcome extends Component
{
    public function render()
    {
        return view('livewire.welcome', [
            'greeting' => now()->hour < 18 ? 'Bonjour' : 'Bonsoir',
            'members' => auth()->user()->family->users()->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
