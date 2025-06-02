<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card glass-card p-4 shadow">
                <div class="d-flex align-items-center mb-4">
                    <i class="bi bi-whatsapp fs-2 text-success me-2"></i>
                    <h3 class="fw-bold mb-0">WhatsApp Bot</h3>
                </div>

                <div class="row g-4">
                    {{-- Columna izquierda --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            @if ($estado === 'connected')
                                <div class="alert alert-success d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-check-circle-fill me-1"></i> WhatsApp conectado
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="desconectar">
                                        <i class="bi bi-box-arrow-right me-1"></i>Desconectar
                                    </button>
                                </div>
                            @elseif ($estado === 'qr')
                                <div id="qr-container" class="text-center">
                                    <img src="{{ $qr }}" class="img-thumbnail" alt="QR Code" width="200">
                                </div>
                            @else
                                <div class="text-muted text-center">⏳ Esperando conexión…</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="mensaje" class="form-label">Nuevo mensaje</label>
                            <textarea wire:model.defer="nuevoMensaje" class="form-control" rows="3"
                                placeholder="Escribe tu mensaje..."></textarea>
                            <button wire:click="guardarMensaje" class="btn btn-primary w-100 mt-2">
                                <i class="bi bi-save me-1"></i> Guardar mensaje
                            </button>
                        </div>

                        <div class="mb-3">
                            <label for="excel" class="form-label">Subir archivo Excel</label>
                            <input type="file" wire:model="archivoExcel" class="form-control" accept=".xlsx">
                            <button wire:click="enviarExcel" class="btn btn-success w-100 mt-2">
                                <i class="bi bi-send-check me-1"></i> Enviar mensajes
                            </button>
                        </div>

                        @if (session()->has('mensaje'))
                            <div class="alert alert-success mt-2">{{ session('mensaje') }}</div>
                        @elseif (session()->has('error'))
                            <div class="alert alert-danger mt-2">{{ session('error') }}</div>
                        @endif
                    </div>

                    {{-- Columna derecha --}}
                    <div class="col-md-8">
                        <h5>Mensajes guardados</h5>
                        <ul class="list-group" style="max-height: 270px; overflow-y: auto">
                            @forelse ($mensajes as $m)
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $m['texto'] }}
                                    <button class="btn btn-sm btn-danger"
                                        wire:click="eliminarMensaje({{ $m['id'] }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </li>
                            @empty
                                <li class="list-group-item">No hay mensajes guardados.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
