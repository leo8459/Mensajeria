<div>
    {{-- controles --}}
    <div class="mb-3 d-flex flex-wrap gap-2">
        <input wire:model.defer="scan"
               wire:keydown.enter="addByScan"
               class="form-control w-auto"
               placeholder="Escanea o escribe CODIGO y Enter">

        <button wire:click="enviarSeleccionados"
                class="btn btn-primary"
                @disabled(!$seleccion)>
            📤 Enviar seleccionados
        </button>

        <button wire:click="enviarTodos"
                class="btn btn-primary">
            🚀 Enviar a TODOS
        </button>
    </div>

    {{-- tabla --}}
    <div class="table-responsive shadow rounded">
        <table class="table table-sm table-hover">
            <thead class="table-secondary sticky-top">
                <tr><th>#</th><th>CODIGO</th><th>TELÉFONO</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($paquetes as $i=>$p)
                @php $sel = in_array($p['TELEFONO'], $seleccion); @endphp
                <tr class="{{ $sel ? 'table-primary' : '' }}">
                    <td>{{ $i+1 }}</td>

                    {{-- Clic en CODIGO alterna selección --}}
                    <td style="cursor:pointer"
                        wire:click="toggle('{{ $p['TELEFONO'] }}')">
                        {{ $p['CODIGO'] }}
                    </td>

                    <td>{{ $p['TELEFONO'] }}</td>
                    <td>
                        <button class="btn btn-sm btn-danger"
                                wire:click="toggle('{{ $p['TELEFONO'] }}')">✖</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- lista de mensajes --}}
    <h4 class="mt-4">💬 Mensajes disponibles</h4>
    <ul class="ps-4">
      @foreach($mensajes as $m)<li>{{ $m }}</li>@endforeach
    </ul>

    {{-- alerta simple --}}
    <script>
      window.addEventListener('alert', e => alert(e.detail.mensaje));
    </script>
</div>
