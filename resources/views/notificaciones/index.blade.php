@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto mt-6">
    <h2 class="text-3xl font-bold text-bluey-dark mb-6">Tus notificaciones</h2>

    @forelse ($notifications as $notification)
    {{-- Determina el color del borde según si la notificación está leída o no --}}
    <div class="bg-white shadow p-4 mb-4 rounded border-l-4 {{ $notification->read_at ? 'border-gray-300' : 'border-bluey-primary' }}">
        <div class="flex justify-between items-start">
            <div>
                {{-- Lógica para Notificaciones de Recordatorio --}}
                {{-- Se asume que $notification->data['type'] siempre estará presente --}}
                @if($notification->data['type'] === 'App\\Notifications\\ReminderNotification') {{-- <--- CAMBIO AQUÍ --}}
                <p class="font-semibold text-lg text-bluey-dark">{{ $notification->data['title'] ?? 'Recordatorio Importante' }}</p>
                <p class="text-sm text-gray-700 mt-1">
                    <b>Mascota:</b> {{ $notification->data['pet_name'] ?? 'Desconocida' }} <br>
                    <b>Mensaje:</b> {{ $notification->data['formatted_text'] ?? $notification->data['description'] ?? 'Detalles del recordatorio.' }}
                </p>
                <p class="text-xs text-gray-500 mt-2">
                    Programado para: {{ \Carbon\Carbon::parse($notification->data['remind_at'])->format('d/m/Y H:i A') }}
                </p>
                @if(isset($notification->data['link']))
                <a href="{{ $notification->data['link'] }}" class="inline-block mt-3 px-4 py-2 bg-bluey-primary text-white rounded hover:bg-bluey-dark text-sm font-semibold">
                    Ver Recordatorio
                </a>
                @endif
                {{-- Lógica para Notificaciones de Reprogramación de Cita --}}
                {{-- Se asume que $notification->data['type'] siempre estará presente --}}
                @elseif(($notification->data['type'] === 'reprogramacion' ||
                $notification->data['type'] === 'reprogramacion_propuesta_veterinario')
                && isset($notification->data['reprogramming_request_id'])) {{-- <--- CAMBIO AQUÍ --}}
                @php
                $reprogrammingRequest = null;
                try {
                $reprogrammingRequest = \App\Models\ReprogrammingRequest::find($notification->data['reprogramming_request_id']);
                } catch (\Exception $e) {
                $reprogrammingRequest = null;
                }
                @endphp

                <p class="font-semibold text-lg text-bluey-dark">{{ $notification->data['title'] ?? 'Notificación de Cita' }}</p>
                <p class="text-sm text-gray-700 mt-1">
                    {{ $notification->data['body'] ?? 'Información sobre la cita.' }}
                </p>

                @if ($reprogrammingRequest)
                @if ($reprogrammingRequest->status === 'pending_client_confirmation')
                <form action="{{ route('notifications.reprogramacion.aceptar', $reprogrammingRequest->id) }}" method="POST" class="mt-3" onsubmit="return confirm('¿Estás seguro de que deseas aceptar esta reprogramación? Esta acción es irreversible.');">
                    @csrf
                    <button type="submit" class="inline-block px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded text-sm font-semibold">
                        Aceptar reprogramación
                    </button>
                </form>
                @elseif ($reprogrammingRequest->status === 'accepted_by_both')
                <p class="text-sm text-green-600 mt-2">¡Reprogramación aceptada!</p>
                @elseif ($reprogrammingRequest->status === 'rejected_by_client')
                <p class="text-sm text-red-600 mt-2">Reprogramación rechazada.</p>
                @else
                <p class="text-sm text-gray-500 mt-2">Estado de reprogramación: {{ $reprogrammingRequest->status }}</p>
                @endif
                @else
                <p class="text-sm text-gray-500 mt-2">Detalles de reprogramación no disponibles.</p>
                @endif
                {{-- Lógica para cualquier otro tipo de notificación no especificado --}}
                @else
                <p class="font-semibold text-lg text-bluey-dark">{{ $notification->data['title'] ?? 'Notificación General' }}</p>
                <p class="text-sm text-gray-700 mt-1">
                    {{ $notification->data['body'] ?? 'Haz click para ver los detalles.' }}
                </p>
                @if(isset($notification->data['link']))
                <a href="{{ $notification->data['link'] }}" class="inline-block mt-3 px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 text-sm font-semibold">
                    Ver Detalles
                </a>
                @endif
                @endif
            </div>

            <div class="text-right flex-shrink-0 ml-4">
                <span class="block text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                {{-- Botón para marcar la notificación como leída --}}
                @if (!$notification->read_at)
                <form action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST" class="mt-2">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="text-xs text-blue-500 hover:text-blue-700">
                        Marcar como leída
                    </button>
                </form>
                @endif
                {{-- Botón para eliminar la notificación --}}
                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta notificación?');" class="mt-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700"><i class="bi bi-trash-fill"></i> Eliminar</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <p class="text-gray-500">No tienes notificaciones.</p>
    @endforelse

    <div class="mt-6 text-center">
        @if (Auth::user()->unreadNotifications->count() > 0)
        <form action="{{ route('notifications.mark-all-as-read') }}" method="POST">
            @csrf
            <button type="submit" class="px-6 py-3 bg-bluey-light text-bluey-dark rounded hover:bg-bluey-primary hover:text-white font-semibold">
                Marcar todas como leídas
            </button>
        </form>
        @endif
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection