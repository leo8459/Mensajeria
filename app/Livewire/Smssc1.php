<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
class Smssc1 extends Component
{
    /* datos que la vista usa */
    public array  $paquetes = [];   // filas (CODIGO, TELEFONO…)
    public array  $mensajes = [];   // líneas de mensajes.txt
    public array  $seleccion = [];  // teléfonos marcados
    public string $scan = '';       // input del lector
    public string $origen = 'packagesRDD'; // por defecto

    /* ────────── Ciclo de vida ────────── */
    public function mount($origen = 'packagesRDD'): void
    {
        $this->origen = $origen;

        $url = "http://172.65.10.52/api/{$this->origen}";

        try {
            $this->paquetes = collect(
                Http::withToken(env('API_PACKAGES_TOKEN'))
                    ->get($url)
                    ->json()
            )->filter(fn($p) => $p['TELEFONO'] ?? false)->values()->all();
        } catch (\Exception $e) {
            $this->paquetes = [];
            $this->dispatch('alert', mensaje: "Error al cargar paquetes desde {$this->origen}");
        }

        $this->mensajes = collect(
            preg_split('/\r?\n/', Storage::disk('local')->get('mensajes.txt'))
        )->filter()->values()->all();
    }

    /* ────────── Seleccionar / quitar ────────── */
    public function toggle(string $tel): void
    {
        if (in_array($tel, $this->seleccion)) {
            $this->seleccion = array_diff($this->seleccion, [$tel]);
        } else {
            $this->seleccion[] = $tel;
        }
    }

    /* Enter en el lector */
    public function addByScan(): void
    {
        $code = strtoupper(trim($this->scan));
        $this->scan = '';
        if (!$code) return;

        $row = collect($this->paquetes)->firstWhere('CODIGO', $code);
        if ($row) $this->toggle($row['TELEFONO']);
        else      $this->dispatch('alert', mensaje: "Código $code no encontrado");
    }

    /* ────────── Envío SMS ────────── */
    private function sendTo(array $telefonos): void
    {
        if (!$this->mensajes) {
            $this->dispatch('alert', mensaje: 'mensajes.txt vacío');
            return;
        }

        foreach ($telefonos as $tel) {
            $msg = $this->mensajes[array_rand($this->mensajes)];
            /* llamada directa con Http façade */
            Http::withHeaders([
                'Authorization' => env('SMS_GATEWAY_TOKEN'),
                'Content-Type'  => 'application/json',
            ])->post(env('SMS_GATEWAY_URL'), [
                'to'      => '+591' . $tel,
                'message' => $msg,
            ]);
        }
        $this->dispatch('alert', mensaje: 'SMS enviados');
    }

    public function enviarSeleccionados(): void
    {
        $this->sendTo($this->seleccion);
        $this->seleccion = [];
    }
    public function enviarTodos(): void
    {
        $this->sendTo(array_column($this->paquetes, 'TELEFONO'));
        $this->seleccion = [];
    }
     public function render()
    {
        return view('livewire.smssc1');
    }

}
   
