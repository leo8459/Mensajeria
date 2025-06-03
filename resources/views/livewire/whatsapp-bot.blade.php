<div class="container py-4">
    {{-- barra superior (código + masivo) --}}
    <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
       <input wire:model.defer="scan"
       {{-- ⬇️  al presionar Enter enviamos el valor actual --}}
       wire:keydown.enter.prevent="enviarPorCodigo($event.target.value)"
       class="form-control w-auto"
       placeholder="Pega / escanea CODIGO y Enter">


        <button wire:click="enviarPorCodigo" class="btn btn-success">
            📤 Enviar por código
        </button>

        <button wire:click="enviarTodos" id="btnTodos"
                class="btn btn-primary" @disabled(!$packages)>
            🚀 Enviar TODOS
        </button>
    </div>

    {{-- alertas --}}
    @if (session()->has('mensaje'))
        <div class="alert alert-success">{{ session('mensaje') }}</div>
    @elseif (session()->has('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card glass-card p-4 shadow">
        <div class="row g-5">

            {{-- columna izquierda --}}
            <aside class="col-lg-4">
                {{-- QR / estado --}}
                <div class="mb-3">
                    @if ($estado === 'connected')
                        <div class="alert alert-success d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-check-circle-fill me-1"></i>WhatsApp conectado</span>
                            <button class="btn btn-sm btn-outline-danger" wire:click="desconectar">
                                <i class="bi bi-box-arrow-right me-1"></i>Salir
                            </button>
                        </div>
                    @elseif ($estado === 'qr' && $qr)
                        <div id="qr-container" class="text-center">
                            <img src="{{ $qr }}" class="img-thumbnail" style="max-width:220px">
                        </div>
                    @else
                        <div class="text-muted text-center">⏳ Esperando conexión…</div>
                    @endif
                </div>

                {{-- nuevo mensaje --}}
                <div class="mb-4">
                    <h6 class="fw-semibold mb-2"><i class="bi bi-pencil-square me-1"></i>Nuevo mensaje</h6>
                    <textarea wire:model.defer="nuevoMensaje" rows="3" class="form-control"
                              placeholder="Escribe tu mensaje..."></textarea>
                    <button wire:click="guardarMensaje" class="btn btn-primary w-100 mt-2">
                        <i class="bi bi-save me-1"></i>Guardar
                    </button>
                </div>

                {{-- subir excel --}}
                <div class="mb-4">
                    <h6 class="fw-semibold mb-2"><i class="bi bi-upload me-1"></i>Subir Excel</h6>
                    <input type="file" wire:model="archivoExcel" accept=".xlsx" class="form-control">
                    <button wire:click="enviarExcel" class="btn btn-success w-100 mt-2">
                        <i class="bi bi-send-check me-1"></i>Enviar Mensajes
                    </button>
                    {{-- progreso Livewire --}}
                    <div wire:loading wire:target="enviarExcel" class="small text-secondary mt-1">
                        <i class="bi bi-arrow-repeat spin me-1"></i>Enviando…
                    </div>
                </div>
            </aside>

            {{-- columna derecha --}}
            <section class="col-lg-8">
                {{-- mensajes guardados --}}
                <h6 class="fw-semibold mb-2"><i class="bi bi-chat-dots me-1"></i>Mensajes guardados</h6>
                <ul class="list-group mb-4" style="max-height: 270px; overflow-y:auto">
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

                {{-- paquetes RDD --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-box-seam me-1"></i>Paquetes RDD</h6>
                    <button class="btn btn-sm btn-outline-primary" wire:click="cargarPackages">
                        Actualizar
                    </button>
                </div>
                <div class="table-responsive" style="max-height:300px; overflow-y:auto">
                    <table class="table table-hover table-sm align-middle mb-0">
                        @if ($packages)
                            <thead class="table-light sticky-top">
                                <tr>
                                    @foreach (array_keys($packages[0]) as $c)
                                        <th>{{ $c }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($packages as $p)
                                    <tr>
                                        @foreach ($p as $v) <td>{{ $v }}</td> @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        @else
                            <tbody><tr><td class="text-center">Sin datos.</td></tr></tbody>
                        @endif
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
