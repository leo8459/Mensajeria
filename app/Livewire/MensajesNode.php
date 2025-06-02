<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class MensajesNode extends Component
{
    public $mensajes = [];

    public function mount()
    {
        $this->getMensajes();
    }

    public function getMensajes()
    {
        $res = Http::get(config('services.nodewa.url') . '/mensajes');
        $this->mensajes = $res->successful() ? $res->json() : [];
    }

    public function eliminar($id)
    {
        Http::delete(config('services.nodewa.url') . "/mensajes/{$id}");
        $this->getMensajes();
    }

    public function render()
    {
        return view('livewire.mensajes-node');
    }
}
