<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Eventos;

class Evento extends Component
{
     public $eventos;

    public function mount(): void
    {
        // Trae los eventos más recientes primero
        $this->eventos = Eventos::with('user')->latest()->get();
    }

    public function render()
    {
        return view('livewire.evento');
    }
}
