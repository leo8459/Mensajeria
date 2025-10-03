<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Dnd1 extends Component
{
     use WithFileUploads;

    /* ─────────── Tabs de la IU ─────────── */
    public string $uiTab = 'crear';                 // crear | todos | prelista | excel
    public function seleccionarTab(string $tab): void
    {
        $this->uiTab = $tab;
    }

    /* ─────────── cuenta ─────────── */
    public string $acc = 'dnd1';                     // wa1 | wa2 | wa3

    /* ─────────── datos y estados ─────────── */
    public array  $prelista  = [];                 // números acumulados por Enter
    public array  $mensajes  = [];                 // textos guardados
    public array  $packages  = [];                 // datos /packages
    public array  $envios    = [];                 // registro de envíos
    public        $qr, $estado = 'pending';
    public string $nuevoMensaje = '';
    public string $scan         = '';              // input CODIGO
    public        $archivoExcel;                   // file

    /* ─────────── ciclo de vida ─────────── */
    public function mount(string $acc = 'dnd1')
    {
        $this->acc = $acc;
        $this->actualizarQR();
        $this->cargarMensajes();
        $this->cargarPackages();
    }

    /* ═════════════ MENSAJES CRUD ═════════════ */
    public function cargarMensajes()
    {
        $r = Http::get(config('services.nodewa.url') . '/mensajes');
        $this->mensajes = $r->successful() ? $r->json() : [];
    }

    public function guardarMensaje()
    {
        if (!trim($this->nuevoMensaje)) return;

        Http::post(config('services.nodewa.url') . '/mensajes', [
            'texto' => trim($this->nuevoMensaje),
        ]);

        $this->nuevoMensaje = '';
        $this->cargarMensajes();
        session()->flash('mensaje', 'Mensaje guardado.');
    }

    public function eliminarMensaje($id)
    {
        Http::delete(config('services.nodewa.url') . "/mensajes/{$id}");
        $this->cargarMensajes();
    }

    /* ════════════ QR / CONEXIÓN WA ════════════ */
    public function actualizarQR()
    {
        $r = Http::get(config('services.nodewa.url') . "/{$this->acc}/qr");
        if (!$r->successful()) return;

        $d            = $r->json();
        $this->estado = $d['status'] ?? 'pending';
        $this->qr     = $d['status'] === 'qr' ? $d['src'] : null;
    }

    public function desconectar()
    {
        $ok = Http::post(config('services.nodewa.url') . "/{$this->acc}/logout")
            ->json('success');

        if ($ok) {
            $this->estado = 'pending';
            $this->qr     = null;
        }
    }

    /* ══════════════ PAQUETES RDD ═════════════ */
    public function cargarPackages()
    {
        $r = Http::get(config('services.nodewa.url') . "/{$this->acc}/packages");
        $this->packages = $r->successful() ? $r->json() : [];
    }

    /* ══════════════ PRELISTA ═════════════ */
    public function agregarAPrelista()
    {
        $code       = strtoupper(trim($this->scan));
        $this->scan = '';

        if (!$code) return;

        $fila = collect($this->packages)
            ->first(fn($p) => strtoupper($p['CODIGO'] ?? '') === $code);

        if (!$fila)      return session()->flash('error', '❌ Código no encontrado.');
        $tel = preg_replace('/\D/', '', $fila['TELEFONO'] ?? '');
        if (!preg_match('/^\d{7,15}$/', $tel))
            return session()->flash('error', '❌ Teléfono inválido.');

        $wa = "591{$tel}@c.us";

        if (!in_array($wa, $this->prelista, true)) {
            array_unshift($this->prelista, $wa);
            session()->flash('mensaje', 'Número agregado a prelista.');
        } else {
            session()->flash('mensaje', 'Número ya estaba en la prelista.');
        }
    }

    public function eliminarDePrelista(int $i)
    {
        if (isset($this->prelista[$i])) {
            array_splice($this->prelista, $i, 1);
        }
    }

    public function mandarPrelista()
    {
        set_time_limit(0);
        $this->envios = [];                         // reinicia log

        if (!$this->mensajes) return session()->flash('error', '⚠️ No hay mensajes.');
        if (!$this->prelista) return session()->flash('error', '⚠️ Prelista vacía.');

        foreach ($this->prelista as $idx => $to) {
            $msg = $this->mensajes[array_rand($this->mensajes)]['texto'] ?? '';
            Http::post(config('services.nodewa.url') . "/{$this->acc}/send", [
                'to'      => $to,
                'message' => $msg,
            ]);

            $this->envios[] = [
                'codigo'   => '(prelista)',
                'telefono' => substr($to, 3, -4),   // quita 591 y @c.us
                'texto'    => $msg,
            ];

            if ($idx < count($this->prelista) - 1) {
                sleep(180 + rand(0, 120));          // 3 min + 0-2 min
            }
        }

        $this->prelista = [];
        session()->flash('mensaje', '🏁 Mensajes enviados a prelista.');
    }

    /* ═════════ ENVÍOS DIRECTOS ═════════ */
    public function enviarPorCodigo(string $code = null)
    {
        if (!is_null($code)) $this->scan = strtoupper(trim($code));
        $code       = strtoupper(trim($this->scan));
        $this->scan = '';

        if (!$code)           return session()->flash('error', '⚠️ Ingresa un código.');
        if (!$this->mensajes) return session()->flash('error', '⚠️ No hay mensajes.');

        $fila = collect($this->packages)
            ->first(fn($p) => strtoupper($p['CODIGO'] ?? '') === $code);

        if (!$fila)           return session()->flash('error', '❌ Código no encontrado.');

        $tel = preg_replace('/\D/', '', $fila['TELEFONO'] ?? '');
        if (!preg_match('/^\d{7,15}$/', $tel))
            return session()->flash('error', '❌ Teléfono inválido.');

        $msg = $this->mensajes[array_rand($this->mensajes)]['texto'] ?? '';

        $ok = Http::post(config('services.nodewa.url') . "/{$this->acc}/send", [
            'to'      => "591{$tel}@c.us",
            'message' => $msg,
        ])->json('success');

        $this->envios[] = [
            'codigo'   => $code,
            'telefono' => $tel,
            'texto'    => $msg,
        ];

        $ok
            ? session()->flash('mensaje', "✅ Mensaje enviado a {$tel}.")
            : session()->flash('error',   '❌ Error al enviar.');
    }

    public function enviarTodos()
    {
        set_time_limit(0);
        $this->envios = [];

        if (!$this->mensajes) return session()->flash('error', '⚠️ No hay mensajes.');
        if (!$this->packages) return session()->flash('error', '⚠️ Tabla vacía.');

        foreach ($this->packages as $idx => $p) {
            $tel = preg_replace('/\D/', '', $p['TELEFONO'] ?? '');
            if (!preg_match('/^\d{7,15}$/', $tel)) continue;

            $msg = $this->mensajes[array_rand($this->mensajes)]['texto'] ?? '';
            Http::post(config('services.nodewa.url') . "/{$this->acc}/send", [
                'to'      => "591{$tel}@c.us",
                'message' => $msg,
            ]);

            $this->envios[] = [
                'codigo'   => $p['CODIGO'] ?? '',
                'telefono' => $tel,
                'texto'    => $msg,
            ];

            if ($idx < count($this->packages) - 1) {
                sleep(180 + rand(0, 120));
            }
        }

        session()->flash('mensaje', '🚀 Mensajes enviados a TODOS.');
    }

    /* ═════════ EXCEL MASIVO ═════════ */
    public function enviarExcel()
    {
        $this->validate(['archivoExcel' => 'required|file|mimes:xlsx,xls|max:2048']);

        $tmp  = $this->archivoExcel->store('tmp');
        $name = $this->archivoExcel->getClientOriginalName();

        $r = Http::attach('excel', Storage::get($tmp), $name)
            ->post(config('services.nodewa.url') . '/enviar-excel');

        Storage::delete($tmp);
        $this->reset('archivoExcel');

        $r->successful() && $r->json('success')
            ? session()->flash('mensaje', '📤 Envío programado.')
            : session()->flash('error',   '❌ Error al enviar el Excel.');
    }
    public function render()
    {
        return view('livewire.dnd1');
    }
}
