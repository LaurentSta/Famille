<?php

namespace App\Livewire;

use Livewire\Component;

class AiChat extends Component
{
    public function render()
    {
        return view('livewire.ai-chat', [
            'configured' => filled(config('services.deepseek.key')),
        ])->layout('layouts.app');
    }
}
