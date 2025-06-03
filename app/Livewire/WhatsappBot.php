<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class WhatsappBot extends Component
{
    use WithFileUploads;

    /* ───── estado UI ───── */
    public $qr, $estado = 'pending';

    /* ───── mensajes (CRUD) ───── */
    public $mensajes       = [];     // textos guardados
    public $nuevoMensaje   = '';

    /* ───── paquetes RDD ───── */
    public $packages       = [];     // datos de /packages
    public $scan           = '';     // input CODIGO

    /* ───── Excel masivo ───── */
    public $archivoExcel;

    /* ───── hooks ───── */
    public function mount()
    {
        $this->actualizarQR();
        $this->cargarMensajes();
        $this->cargarPackages();
    }

    /* ╔══════════════════════════════════╗
       ║   SECCIÓN:  mensajes (CRUD)       ║
       ╚══════════════════════════════════╝ */

    public function cargarMensajes()
    {
        $res = Http::get(config('services.nodewa.url') . '/mensajes');
        $this->mensajes = $res->successful() ? $res->json() : [];
    }

    public function guardarMensaje()
    {
        if (!trim($this->nuevoMensaje)) return;

        Http::post(config('services.nodewa.url') . '/mensajes', [
            'texto' => trim($this->nuevoMensaje),
        ]);

        $this->nuevoMensaje = '';
        $this->cargarMensajes();
    }

    public function eliminarMensaje($id)
    {
        Http::delete(config('services.nodewa.url') . "/mensajes/{$id}");
        $this->cargarMensajes();
    }

    /* ╔══════════════════════════════════╗
       ║   SECCIÓN:  QR / conexión WA      ║
       ╚══════════════════════════════════╝ */

    public function actualizarQR()
    {
        $res = Http::get(config('services.nodewa.url') . '/qr');
        if (!$res->successful()) return;

        $data        = $res->json();
        $this->estado = $data['status'] ?? 'pending';
        $this->qr     = $data['status'] === 'qr' ? $data['src'] : null;
    }

    public function desconectar()
    {
        $ok = Http::post(config('services.nodewa.url') . '/logout')->json('success');
        if ($ok) {
            $this->estado = 'pending';
            $this->qr     = null;
            $this->cargarMensajes();
        }
    }

    /* ╔══════════════════════════════════╗
       ║   SECCIÓN:  paquetes RDD         ║
       ╚══════════════════════════════════╝ */

    public function cargarPackages()
    {
        $r = Http::get(config('services.nodewa.url') . '/packages');
        $this->packages = $r->successful() ? $r->json() : [];
    }

    /* ╔══════════════════════════════════╗
       ║   SECCIÓN:  envíos WhatsApp      ║
       ╚══════════════════════════════════╝ */

    /** Enviar un solo número filtrando por CODIGO */
  /** Enviar un solo número filtrando por CODIGO */
public function enviarPorCodigo(string $code = null)
{
    /* ── 1. Valor obtenido al presionar Enter ── */
    if (!is_null($code)) {
        // viene de wire:keydown.enter → usamos ese valor
        $this->scan = strtoupper(trim($code));
    }

    /* ── 2. Normalizamos y limpiamos la caja ── */
    $code = strtoupper(trim($this->scan));
    $this->scan = '';                           // limpia el input en la vista

    /* ── 3. Validaciones rápidas ── */
    if (!$code)               return session()->flash('error', '⚠️ Ingresa un código.');
    if (!$this->mensajes)     return session()->flash('error', '⚠️ No hay mensajes.');

    /* ── 4. Buscamos el paquete con ese código ── */
    $fila = collect($this->packages)
            ->first(fn ($p) => strtoupper($p['CODIGO'] ?? '') === $code);

    if (!$fila)               return session()->flash('error', '❌ Código no encontrado.');

    /* ── 5. Teléfono y mensaje aleatorio ── */
    $tel = preg_replace('/\D/', '', $fila['TELEFONO'] ?? '');
    if (!preg_match('/^\d{7,15}$/', $tel))
        return session()->flash('error', '❌ Teléfono inválido.');

    $mensaje = $this->mensajes[array_rand($this->mensajes)]['texto'] ?? '';

    /* ── 6. Llamada a la API Node ── */
    $ok = Http::post(config('services.nodewa.url') . '/send', [
        'to'      => "591{$tel}@c.us",
        'message' => $mensaje,
    ])->json('success');

    /* ── 7. Resultado al usuario ── */
    $ok
        ? session()->flash('mensaje', "✅ Mensaje enviado a {$tel}.")
        : session()->flash('error',   '❌ Error al enviar.');
}


    /** Enviar a todos los registros actuales de $packages */
    public function enviarTodos()
    {
        if (!$this->mensajes)  return session()->flash('error', '⚠️ No hay mensajes.');
        if (!$this->packages)  return session()->flash('error', '⚠️ Tabla vacía.');

        foreach ($this->packages as $p) {
            $tel = preg_replace('/\D/', '', $p['TELEFONO'] ?? '');
            if (!preg_match('/^\d{7,15}$/', $tel)) continue;

            $mensaje = $this->mensajes[array_rand($this->mensajes)]['texto'] ?? '';
            Http::post(config('services.nodewa.url') . '/send', [
                'to'      => "591{$tel}@c.us",
                'message' => $mensaje,
            ]);
            usleep(random_int(60, 200) * 1000);   //   ~0.06-0.2 s entre envíos
        }

        session()->flash('mensaje', '🚀 Mensajes enviados a TODOS.');
    }

    /* ╔══════════════════════════════════╗
       ║   SECCIÓN:  Excel masivo         ║
       ╚══════════════════════════════════╝ */

    public function enviarExcel()
    {
        $this->validate(['archivoExcel' => 'required|file|mimes:xlsx,xls|max:2048']);

        $tmpPath   = $this->archivoExcel->store('tmp');
        $original  = $this->archivoExcel->getClientOriginalName();

        $resp = Http::attach('excel', Storage::get($tmpPath), $original)
                    ->post(config('services.nodewa.url') . '/enviar-excel');

        Storage::delete($tmpPath);
        $this->reset('archivoExcel');

        $resp->successful() && $resp->json('success')
            ? session()->flash('mensaje', '📤 Envío programado.')
            : session()->flash('error',   '❌ Error al enviar el Excel.');
    }

    /* ───── render ───── */
    public function render()
    {
        return view('livewire.whatsapp-bot');
    }
}
