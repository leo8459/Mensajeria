<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class WhatsappBot extends Component
{
    use WithFileUploads;

    public $mensajes = [];
    public $nuevoMensaje;
    public $qr;
    public $estado = 'pending';
    public $archivoExcel;

    public function mount()
    {
        $this->actualizarQR();
        $this->cargarMensajes();
    }

    public function cargarMensajes()
    {
        $res = Http::get(config('services.nodewa.url') . '/mensajes');
        $this->mensajes = $res->successful() ? $res->json() : [];
    }

    public function guardarMensaje()
    {
        if (!$this->nuevoMensaje) return;

        Http::post(config('services.nodewa.url') . '/mensajes', [
            'texto' => $this->nuevoMensaje,
        ]);

        $this->nuevoMensaje = '';
        $this->cargarMensajes();
    }

    public function eliminarMensaje($id)
    {
        Http::delete(config('services.nodewa.url') . "/mensajes/{$id}");
        $this->cargarMensajes();
    }

    public function actualizarQR()
    {
        $res = Http::get(config('services.nodewa.url') . '/qr');
        if ($res->successful()) {
            $data = $res->json();
            $this->estado = $data['status'];
            if ($data['status'] === 'qr') {
                $this->qr = $data['src'];
            }
        }
    }

  public function enviarExcel()
{
    // 1️⃣  validación rápida
    $this->validate([
        'archivoExcel' => 'required|file|mimes:xlsx,xls|max:2048',
    ]);

    // 2️⃣  ahora sí podemos usar el objeto UploadedFile
    $tmpPath = $this->archivoExcel->store('temp');          // genera nombre único
    $original = $this->archivoExcel->getClientOriginalName();

    $response = Http::attach(
        'excel',
        Storage::get($tmpPath),
        $original
    )->post(config('services.nodewa.url') . '/enviar-excel');

    Storage::delete($tmpPath);                              // limpia temp

    if ($response->successful() && $response->json('success')) {
        session()->flash('mensaje', '📤 Envío programado correctamente.');
    } else {
        session()->flash('error', '❌ Hubo un error al enviar el archivo.');
    }

    // resetea input
    $this->reset('archivoExcel');
}


    public function desconectar()
    {
        $res = Http::post(config('services.nodewa.url') . '/logout');
        if ($res->successful()) {
            $this->estado = 'pending';
            $this->qr = null;
            $this->cargarMensajes();
        }
    }

    public function render()
    {
        return view('livewire.whatsapp-bot');
    }
}

