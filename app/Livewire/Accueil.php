<?php

namespace App\Livewire;

use Livewire\Component;

class Accueil extends Component
{
    public function render()
    {
        $famille = auth()->user()->famille;

        return view('livewire.accueil', [
            'greeting' => now()->hour < 18 ? 'Bonjour' : 'Bonsoir',
            'members' => $famille?->users()->orderBy('name')->get() ?? collect(),
        ])->layout('layouts.app');
    }
}
