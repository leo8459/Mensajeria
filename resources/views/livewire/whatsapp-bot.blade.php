{{-- resources/views/livewire/whatsapp-bot.blade.php --}}
<div>

    {{-- ≡≡ Barra superior: código, envío masivo y prelista ≡≡ --}}
    <div class="container my-3 d-flex flex-wrap gap-2 justify-content-end">
        <input wire:model.defer="scan"
               wire:keydown.enter.prevent="agregarAPrelista"
               class="form-control w-auto"
               placeholder="Pega / escanea CODIGO y Enter">

        <button class="btn btn-success" wire:click="enviarPorCodigo">📤 Enviar por código</button>
        <button id="btnTodos" class="btn btn-primary" wire:click="enviarTodos" @disabled(!$packages)>
            🚀 Enviar TODOS
        </button>
        <button class="btn btn-warning" wire:click="mandarPrelista">📨 Mandar Prelista</button>
    </div>

    {{-- prelista visual --}}
    <div class="container">
        <ul class="list-group scroll-y mt-2">
            @forelse ($prelista as $i => $nro)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $nro }}
                    <button class="btn btn-sm btn-outline-danger" wire:click="eliminarDePrelista({{ $i }})">
                        <i class="bi bi-x"></i>
                    </button>
                </li>
            @empty
                <li class="list-group-item text-muted">Prelista vacía.</li>
            @endforelse
        </ul>
    </div>

    {{-- alertas --}}
    <div class="container">
        @if (session()->has('mensaje'))
            <div class="alert alert-success">{{ session('mensaje') }}</div>
        @elseif (session()->has('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
    </div>

    {{-- ≡≡ Tarjeta principal “glass” ≡≡ --}}
    <main class="glass-card p-4 container mb-4">
        <div class="row g-5">

            {{-- ── Columna izquierda ── --}}
            <aside class="col-lg-4">

                {{-- QR / estado --}}
                <div class="mb-4 text-center">
                    @switch($estado)
                        @case('connected')
                            <div class="alert alert-success d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-check-circle-fill me-1"></i>WhatsApp conectado</span>
                                <button class="btn btn-sm btn-outline-light text-danger" wire:click="desconectar">
                                    <i class="bi bi-box-arrow-right me-1"></i>Salir
                                </button>
                            </div>
                            @break

                        @case('qr')
                            <img src="{{ $qr }}" class="img-thumbnail" style="max-width:220px">
                            @break

                        @default
                            <span class="small text-muted">⏳ Esperando conexión…</span>
                    @endswitch
                </div>

                {{-- Nuevo mensaje --}}
                <h6 class="fw-semibold mb-2"><i class="bi bi-pencil-square me-1"></i>Nuevo mensaje</h6>
                <textarea wire:model.defer="nuevoMensaje" rows="3" class="form-control mb-2" placeholder="Escribe tu mensaje…"></textarea>
                <button class="btn btn-primary w-100 mb-4" wire:click="guardarMensaje">
                    <i class="bi bi-save me-1"></i>Guardar
                </button>

                {{-- Subir Excel --}}
                <h6 class="fw-semibold mb-2"><i class="bi bi-upload me-1"></i>Subir Excel</h6>
                <input type="file" wire:model="archivoExcel" accept=".xlsx" class="form-control">
                <button class="btn btn-success w-100 mt-2" wire:click="enviarExcel">
                    <i class="bi bi-send-check me-1"></i>Enviar Mensajes
                </button>
                <div wire:loading wire:target="enviarExcel" class="small text-secondary mt-1">
                    <i class="bi bi-arrow-repeat spin me-1"></i>Enviando…
                </div>
            </aside>

            {{-- ── Columna derecha ── --}}
            <section class="col-lg-8">

                {{-- Mensajes guardados --}}
                <h6 class="fw-semibold mb-2"><i class="bi bi-chat-dots me-1"></i>Mensajes guardados</h6>
                <ul class="list-group scroll-y mb-4">
                    @forelse ($mensajes as $m)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $m['texto'] }}</span>
                            <button class="btn btn-sm btn-danger" wire:click="eliminarMensaje({{ $m['id'] }})">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </li>
                    @empty
                        <li class="list-group-item">No hay mensajes guardados.</li>
                    @endforelse
                </ul>

                {{-- Paquetes --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-box-seam me-1"></i>Paquetes RDD</h6>
                    <button class="btn btn-sm btn-outline-primary" wire:click="cargarPackages">Actualizar</button>
                </div>

                <div class="table-responsive scroll-y" style="max-height:300px">
                    <table class="table table-hover table-sm align-middle mb-0">
                        @if ($packages)
                            <thead class="table-light">
                                <tr>@foreach (array_keys($packages[0]) as $c)<th>{{ $c }}</th>@endforeach</tr>
                            </thead>
                            <tbody>
                                @foreach ($packages as $p)
                                    <tr>@foreach ($p as $v)<td>{{ $v }}</td>@endforeach</tr>
                                @endforeach
                            </tbody>
                        @else
                            <tbody><tr><td class="text-center">Sin datos.</td></tr></tbody>
                        @endif
                    </table>
                </div>
            </section>
        </div>
    </main>

    <footer class="w-100 text-center py-3 small">&copy; {{ date('Y') }} – Bots Corp</footer>

</div>
