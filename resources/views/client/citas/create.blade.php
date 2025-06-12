{{-- resources/views/client/citas/create.blade.php --}}

@extends('layouts.app')

@section('title', 'Agendar Nueva Cita')

@section('content')
    <header>
        <div class="logo">
            <img src="#" alt="BlueyVet Logo"> {{-- Reemplaza # con la ruta real de tu logo --}}
            <h1>BlueyVet</h1>
        </div>
        <p>Sistema de Agendamiento de Citas</p>
    </header>

    <div class="container mx-auto p-6 bg-white shadow-md rounded-lg">
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Agendar Nueva Cita</h2>

        {{-- Mensajes de validación y de sesión --}}
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">¡Oops!</strong>
                <span class="block sm:inline">Hubo algunos problemas con tu solicitud:</span>
                <ul class="mt-3 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Información:</strong>
                <span class="block sm:inline">{{ session('info') }}</span>
            </div>
        @endif
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">¡Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif


        <form action="{{ route('client.citas.store') }}" method="POST">
            @csrf {{-- ¡IMPORTANTE! Token CSRF para seguridad en formularios Laravel --}}

            <div class="mb-4">
                <label for="mascota_id" class="block text-gray-700 text-sm font-bold mb-2">Tu Mascota:</label>
                <select name="mascota_id" id="mascota_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('mascota_id') border-red-500 @enderror" required>
                    <option value="">Selecciona una mascota</option>
                    @foreach ($mascotas as $mascota)
                        <option value="{{ $mascota->id }}" {{ old('mascota_id') == $mascota->id ? 'selected' : '' }}>
                            {{ $mascota->name }} ({{ $mascota->species }})
                        </option>
                    @endforeach
                </select>
                @error('mascota_id')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="service_id" class="block text-gray-700 text-sm font-bold mb-2">Tipo de Servicio:</label>
                <select name="service_id" id="service_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('service_id') border-red-500 @enderror" required>
                    <option value="">Selecciona un servicio</option>
                    @foreach ($allServices as $service)
                        <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }} (S/.{{ number_format($service->price, 2) }})
                            @if (in_array($service->id, $purchasedServiceIds))
                                <span class="text-green-600">(Ya Adquirido)</span>
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('service_id')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="reason" class="block text-gray-700 text-sm font-bold mb-2">Motivo de la Consulta (opcional):</label>
                <textarea name="reason" id="reason" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('reason') border-red-500 @enderror" placeholder="Describe brevemente el motivo de la consulta...">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="veterinarian_id" class="block text-gray-700 text-sm font-bold mb-2">Veterinario:</label>
                <select name="veterinarian_id" id="veterinarian_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('veterinarian_id') border-red-500 @enderror" required>
                    <option value="">Selecciona un veterinario</option>
                    @foreach ($veterinarians as $vet)
                        <option value="{{ $vet->id }}" {{ old('veterinarian_id') == $vet->id ? 'selected' : '' }}>
                            {{ $vet->user->name }}
                        </option>
                    @endforeach
                </select>
                @error('veterinarian_id')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="date" class="block text-gray-700 text-sm font-bold mb-2">Fecha y Hora:</label>
                <input type="datetime-local" name="date" id="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('date') border-red-500 @enderror" value="{{ old('date') }}" required>
                @error('date')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <i class="fas fa-calendar-check mr-2"></i> Reservar Cita
            </button>

            <a href="{{ route('client.citas.index') }}" class="ml-4 inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <i class="fas fa-arrow-left mr-2"></i> Volver a Mis Citas
            </a>
        </form>
    </div>
@endsection

@push('js')
    {{-- Si tienes JavaScript específico para el date/time picker o alguna UI, puedes añadirlo aquí --}}
    {{-- El archivo `citas.js` que tenías, si maneja la lógica de envío de formulario, ya no es necesario
         porque el formulario se enviará vía Laravel POST. Si tenía otra funcionalidad (ej. validaciones JS
         antes de enviar, o manipulaciones de UI), deberías integrarla aquí. --}}
@endpush