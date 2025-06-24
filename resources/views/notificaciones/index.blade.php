@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto mt-6">
    <h2 class="text-xl font-bold mb-4">Tus notificaciones</h2>

    @forelse ($notifications as $notification)
    <div class="bg-white shadow p-4 mb-4 rounded">
        <p class="font-semibold">{{ $notification->data['title'] ?? 'Notificación' }}</p>
        <p class="text-sm text-gray-600">{{ $notification->data['body'] ?? $notification->type }}</p>
        <p class="text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>

        {{-- Elimina esta línea de depuración cuando todo funcione --}}
        {{-- <pre>{{ print_r($notification->data, true) }}</pre> --}}

        {{-- Lógica para el botón de Aceptar/Estado de Reprogramación --}}
        @php
        $reprogrammingRequest = null;
        $isReprogrammingNotification = false;

        // Verificar si es una notificación de reprogramación (antigua o nueva)
        if (isset($notification->data['type']) && (
        $notification->data['type'] === 'reprogramacion' || // Tipo de notificaciones antiguas
        $notification->data['type'] === 'reprogramacion_propuesta_veterinario' // Tipo de notificaciones nuevas
        ) && isset($notification->data['reprogramming_request_id'])) {
        $isReprogrammingNotification = true;
        try {
        // Cargar la ReprogrammingRequest asociada
        $reprogrammingRequest = \App\Models\ReprogrammingRequest::find($notification->data['reprogramming_request_id']);
        } catch (\Exception $e) {
        // En caso de que el ID no sea válido o el registro haya sido eliminado.
        $reprogrammingRequest = null;
        }
        }
        @endphp

        {{-- Mostrar el botón solo si es una notificación de reprogramación PENDIENTE del cliente --}}
        @if ($isReprogrammingNotification && $reprogrammingRequest && $reprogrammingRequest->status === 'pending_client_confirmation')
        <form action="{{ route('notifications.reprogramacion.aceptar', $reprogrammingRequest->id) }}" method="POST" class="mt-2" onsubmit="return confirm('¿Estás seguro de que deseas aceptar esta reprogramación? Esta acción es irreversible.');">
            @csrf
            <button type="submit" class="text-xs bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded">
                Aceptar reprogramación
            </button>
        </form>
        {{-- Mostrar un mensaje si la reprogramación ya fue aceptada --}}
        @elseif ($isReprogrammingNotification && $reprogrammingRequest && $reprogrammingRequest->status === 'accepted_by_both')
        <p class="text-sm text-green-600 mt-2">¡Reprogramación aceptada!</p>
        {{-- Mostrar un mensaje si la reprogramación fue rechazada (si implementas esa lógica) --}}
        @elseif ($isReprogrammingNotification && $reprogrammingRequest && $reprogrammingRequest->status === 'rejected_by_client')
        <p class="text-sm text-red-600 mt-2">Reprogramación rechazada.</p>
        {{-- Opcional: Si la reprogramación no existe o está en otro estado inesperado --}}
        @elseif ($isReprogrammingNotification && !$reprogrammingRequest)
        <p class="text-sm text-gray-500 mt-2">Detalles de reprogramación no disponibles.</p>
        @endif

        {{-- Opcional: Botón para marcar la notificación como leída (si no se hace automáticamente con la acción) --}}
        @if (!$notification->read())
        <form action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('PUT') {{-- ¡Importante! Laravel necesita esto para métodos PUT --}}
            <button type="submit" class="text-blue-500 hover:text-blue-700 text-sm">
                Marcar como leída
            </button>
        </form>
        @endif
    </div>

    @empty
    <p class="text-gray-500">No tienes notificaciones.</p>
    @endforelse

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection