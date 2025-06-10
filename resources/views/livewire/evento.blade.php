{{-- resources/views/livewire/evento-index.blade.php --}}
<div class="container py-4">

    <h2 class="fw-bold mb-4 text-primary">Lista de eventos</h2>

    <table class="table table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Código</th>
                <th>Teléfono</th>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>Creado</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($eventos as $ev)
                <tr>
                    <td>{{ $ev->id }}</td>
                    <td>{{ $ev->codigo }}</td>
                    <td>{{ $ev->telefono }}</td>
                    <td>{{ $ev->user->name ?? '—' }}</td>
                    <td>{{ $ev->nombre }}</td>
                    <td>{{ $ev->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No hay eventos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>
