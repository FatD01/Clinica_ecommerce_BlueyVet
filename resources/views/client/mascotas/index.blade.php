@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-bluey-light py-8 px-4 sm:px-6">
    <div class="max-w-7xl mx-auto">
        {{-- Card Container --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            {{-- Card Header --}}
            <div class="bg-bluey-dark px-6 py-4 flex flex-col sm:flex-row justify-between items-center">
                <h2 class="text-2xl font-bold text-white">Mis Mascotas</h2>
                <a href="{{ route('client.mascotas.create') }}" 
                   class="mt-3 sm:mt-0 bg-bluey-light-yellow hover:bg-bluey-gold-yellow text-bluey-dark font-bold py-2 px-6 rounded-lg transition-colors duration-300 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Registrar Nueva Mascota
                </a>
            </div>

            {{-- Card Body --}}
            <div class="p-6">
                @if (session('success'))
                    <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                @if ($mascotas->isEmpty())
                    <div class="text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-bluey-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-bluey-dark">No tienes mascotas registradas</h3>
                        <p class="mt-2 text-bluey-dark">¡Anímate a añadir una nueva mascota a tu familia!</p>
                        <div class="mt-6">
                            <a href="{{ route('client.mascotas.create') }}" class="inline-flex items-center px-4 py-2 bg-bluey-primary border border-transparent rounded-md font-semibold text-white hover:bg-bluey-dark transition duration-300">
                                Registrar mi primera mascota
                            </a>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-bluey-light">
                            <thead class="bg-bluey-secondary-light2">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-bluey-dark uppercase tracking-wider">Avatar</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-bluey-dark uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-bluey-dark uppercase tracking-wider">Especie</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-bluey-dark uppercase tracking-wider">Raza</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-bluey-dark uppercase tracking-wider">Peso (kg)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-bluey-dark uppercase tracking-wider">F. Nacimiento</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-bluey-dark uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-bluey-light">
                                @foreach ($mascotas as $mascota)
                                <tr class="hover:bg-bluey-light transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($mascota->hasMedia('avatars'))
                                                <img class="h-10 w-10 rounded-full object-cover border-2 border-bluey-primary" src="{{ $mascota->getFirstMediaUrl('avatars', 'thumb') }}" alt="Avatar de {{ $mascota->name }}">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-bluey-light flex items-center justify-center border-2 border-bluey-primary">
                                                    <svg class="h-6 w-6 text-bluey-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-bluey-dark">{{ $mascota->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-bluey-dark">{{ $mascota->species }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-bluey-dark">{{ $mascota->race ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-bluey-dark">{{ $mascota->weight ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-bluey-dark">{{ $mascota->birth_date ? \Carbon\Carbon::parse($mascota->birth_date)->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('client.mascotas.edit', $mascota) }}" 
                                               class="text-bluey-primary hover:text-bluey-dark transition-colors duration-300"
                                               title="Editar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <form action="{{ route('client.mascotas.destroy', $mascota) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar a {{ $mascota->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-bluey-secondary hover:text-bluey-secondary-light transition-colors duration-300"
                                                        title="Eliminar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection