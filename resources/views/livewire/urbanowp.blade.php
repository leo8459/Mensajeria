{{-- resources/views/livewire/whatsapp-bot.blade.php --}}
<div>

    {{-- ══ Barra superior: input CODIGO + acción rápida ══ --}}
    <div class="container my-3 d-flex flex-wrap gap-2 justify-content-end">
        <input  wire:model.defer="scan"
                wire:keydown.enter.prevent="agregarAPrelista"
                class="form-control w-auto"
                placeholder="Pega / escanea CODIGO y Enter">

        {{-- <button class="btn btn-success" wire:click="enviarPorCodigo">📤 Enviar por código</button> --}}
    </div>

    {{-- ══ Navegación por pestañas (Bootstrap) ══ --}}
    <ul class="nav nav-tabs mb-4">
        @foreach ([
            'crear'    => 'Crear',
            'todos'    => 'Mandar TODOS',
            'prelista' => 'Mandar Prelista',
            'excel'    => 'Excel'
        ] as $clave => $etiqueta)
            <li class="nav-item">
                <button class="nav-link {{ $uiTab === $clave ? 'active' : '' }}"
                        wire:click="seleccionarTab('{{ $clave }}')">
                    {{ $etiqueta }}
                </button>
            </li>
        @endforeach
    </ul>

    {{-- ══ Alertas globales ══ --}}
    <div class="container">
        @if (session()->has('mensaje'))
            <div class="alert alert-success">{{ session('mensaje') }}</div>
        @elseif (session()->has('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
    </div>

    {{-- ══════════ CONTENIDO SEGÚN PESTAÑA ══════════ --}}
    <div class="tab-content">

        {{-- ▸▸ 1) CREAR MENSAJES --}}
        @if ($uiTab === 'crear')
            <main class="glass-card p-4 container mb-4">
                <div class="row g-5">

                    {{-- Columna izquierda --}}
                    <aside class="col-lg-4">

                        {{-- Estado / QR --}}
                        <div class="mb-4 text-center">
                            @switch($estado)
                                @case('connected')
                                    <div class="alert alert-success d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-check-circle-fill me-1"></i>WhatsApp conectado</span>
                                        <button class="btn btn-sm btn-outline-light text-danger"
                                                wire:click="desconectar">
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
                        <textarea wire:model.defer="nuevoMensaje" rows="3" class="form-control mb-2"
                                  placeholder="Escribe tu mensaje…"></textarea>
                        <button class="btn btn-primary w-100 mb-4" wire:click="guardarMensaje">
                            <i class="bi bi-save me-1"></i>Guardar
                        </button>
                    </aside>

                    {{-- Columna derecha --}}
                    <section class="col-lg-8">

                        {{-- Mensajes guardados --}}
                        <h6 class="fw-semibold mb-2"><i class="bi bi-chat-dots me-1"></i>Mensajes guardados</h6>
                        <ul class="list-group scroll-y mb-4">
                            @forelse ($mensajes as $m)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $m['texto'] }}</span>
                                    <button class="btn btn-sm btn-danger"
                                            wire:click="eliminarMensaje({{ $m['id'] }})">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </li>
                            @empty
                                <li class="list-group-item">No hay mensajes guardados.</li>
                            @endforelse
                        </ul>

                        {{-- Tabla de paquetes --}}
                        @if ($packages)
                            <div class="table-responsive scroll-y mb-0" style="max-height:300px">
                                <table class="table table-hover table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>@foreach (array_keys($packages[0]) as $c)<th>{{ $c }}</th>@endforeach</tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($packages as $p)
                                            <tr>@foreach ($p as $v)<td>{{ $v }}</td>@endforeach</tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-center text-muted">Sin datos de paquetes.</p>
                        @endif
                    </section>
                </div>
            </main>
        @endif

        {{-- ▸▸ 2) MANDAR TODOS --}}
        @if ($uiTab === 'todos')
            <div class="container">

                <div class="text-center py-4">
                    <h4 class="mb-3">
                        <i class="bi bi-send-check me-2"></i>Envío masivo a TODOS los paquetes cargados
                    </h4>
                    <button class="btn btn-primary btn-lg"
                            wire:click="enviarTodos"
                            @disabled(!$packages)>
                        🚀 Iniciar envío masivo
                    </button>
                </div>

                {{-- Tabla de paquetes --}}
                @if ($packages)
                    <div class="table-responsive scroll-y mb-4" style="max-height:300px">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>@foreach (array_keys($packages[0]) as $c)<th>{{ $c }}</th>@endforeach</tr>
                            </thead>
                            <tbody>
                                @foreach ($packages as $p)
                                    <tr>@foreach ($p as $v)<td>{{ $v }}</td>@endforeach</tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-muted">Sin datos de paquetes.</p>
                @endif
            </div>
        @endif

        {{-- ▸▸ 3) MANDAR PRELISTA --}}
        @if ($uiTab === 'prelista')
            <div class="container">

                <h5 class="mb-3"><i class="bi bi-list-check me-2"></i>Prelista de envíos</h5>

                {{-- Prelista en tabla --}}
                <div class="table-responsive scroll-y mb-4" style="max-height:300px">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:80px">#</th>
                                <th>Número WhatsApp</th>
                                <th class="text-center" style="width:100px">Quitar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($prelista as $i => $nro)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $nro }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger"
                                                wire:click="eliminarDePrelista({{ $i }})">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Prelista vacía.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <button class="btn btn-warning w-100 mb-4"
                        wire:click="mandarPrelista"
                        @disabled(!count($prelista))>
                    📨 Mandar Prelista
                </button>

                {{-- Tabla de paquetes --}}
                @if ($packages)
                    <div class="table-responsive scroll-y mb-4" style="max-height:300px">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>@foreach (array_keys($packages[0]) as $c)<th>{{ $c }}</th>@endforeach</tr>
                            </thead>
                            <tbody>
                                @foreach ($packages as $p)
                                    <tr>@foreach ($p as $v)<td>{{ $v }}</td>@endforeach</tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-muted">Sin datos de paquetes.</p>
                @endif
            </div>
        @endif

        {{-- ▸▸ 4) EXCEL --}}
        @if ($uiTab === 'excel')
            <div class="container" style="max-width:450px">
                <h5 class="mb-3">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Subir Excel
                </h5>

                <input type="file" wire:model="archivoExcel" accept=".xlsx" class="form-control">

                <button class="btn btn-success w-100 mt-3"
                        wire:click="enviarExcel"
                        @disabled(!$archivoExcel)>
                    <i class="bi bi-send-check me-1"></i>Enviar Mensajes
                </button>

                <div wire:loading wire:target="enviarExcel"
                     class="small text-secondary mt-2">
                    <i class="bi bi-arrow-repeat spin me-1"></i>Enviando…
                </div>
            </div>
        @endif
    </div>

    <footer class="w-100 text-center py-3 small">
        &copy; {{ date('Y') }} – Bots Corp
    </footer>

</div>
