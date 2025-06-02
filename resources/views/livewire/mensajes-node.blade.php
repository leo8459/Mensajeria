<div class="card shadow p-3">
    <h4>Mensajes disponibles</h4>
    <ul class="list-group">
        @forelse ($mensajes as $msg)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $msg['texto'] }}
                <button class="btn btn-sm btn-danger" wire:click="eliminar({{ $msg['id'] }})">🗑️ Eliminar</button>
            </li>
        @empty
            <li class="list-group-item">No hay mensajes</li>
        @endforelse
    </ul>
</div>
