{{-- resources/views/client/citas/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Mis Citas')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Mis Citas Agendadas</h1>

        {{-- Mensajes de éxito o error --}}
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

        <div class="flex justify-end mb-6">
            <a href="{{ route('client.citas.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out">
                <i class="fas fa-plus-circle mr-2"></i> Agendar Nueva Cita
            </a>
        </div>

        @if ($citas->isEmpty())
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
                <p class="font-bold">Aún no tienes citas agendadas.</p>
                <p>Haz clic en "Agendar Nueva Cita" para empezar.</p>
            </div>
        @else
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Mascota
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Servicio
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Veterinario
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Fecha y Hora
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Motivo de Consulta
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Estado
                            </th>
                            {{-- <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100"></th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($citas as $cita)
                            <tr>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $cita->mascota->name }}</p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $cita->service->name }}</p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $cita->veterinarian->user->name }}</p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $cita->date->format('d/m/Y H:i') }}</p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $cita->reason ?? 'N/A' }}</p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                                        <span aria-hidden class="absolute inset-0 {{ $cita->status === 'pending' ? 'bg-yellow-200' : ($cita->status === 'confirmed' ? 'bg-green-200' : 'bg-red-200') }} opacity-50 rounded-full"></span>
                                        <span class="relative text-xs">{{ ucfirst($cita->status) }}</span>
                                    </span>
                                </td>
                                {{-- Si quieres acciones como editar/cancelar --}}
                                {{-- <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-right">
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                    <a href="#" class="text-red-600 hover:text-red-900 ml-3">Cancelar</a>
                                </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection