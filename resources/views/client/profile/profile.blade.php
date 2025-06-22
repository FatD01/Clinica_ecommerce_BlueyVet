@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12 max-w-5xl">
    <div class="text-center mb-10">
        <h1 class="text-4xl font-extrabold text-bluey-dark mb-4">Mi Perfil</h1>
        <div class="w-24 h-1.5 bg-bluey-primary mx-auto mb-6"></div>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Gestiona tu información personal, seguridad y preferencias.
        </p>
    </div>

    <div x-data="{ currentTab: 'personal' }" class="bg-white shadow-xl rounded-lg overflow-hidden flex flex-col md:flex-row">

        <div class="w-full md:w-1/4 bg-bluey-light p-6 border-r border-gray-200">
            <nav class="space-y-2">
                <button
                    @click="currentTab = 'personal'"
                    :class="{ 'bg-bluey-primary text-white': currentTab === 'personal', 'text-bluey-dark hover:bg-bluey-light2': currentTab !== 'personal' }"
                    class="w-full text-left py-3 px-4 rounded-md font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-bluey-primary focus:ring-opacity-50">
                    Información Personal
                </button>
                <button
                    @click="currentTab = 'password'"
                    :class="{ 'bg-bluey-primary text-white': currentTab === 'password', 'text-bluey-dark hover:bg-bluey-light2': currentTab !== 'password' }"
                    class="w-full text-left py-3 px-4 rounded-md font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-bluey-primary focus:ring-opacity-50">
                    Cambiar Contraseña
                </button>
                {{-- Puedes añadir más pestañas aquí: ej. Notificaciones, Historial de Citas, etc. --}}
                {{--
                    <button
                        @click="currentTab = 'notifications'"
                        :class="{ 'bg-bluey-primary text-white': currentTab === 'notifications', 'text-bluey-dark hover:bg-bluey-light2': currentTab !== 'notifications' }"
                        class="w-full text-left py-3 px-4 rounded-md font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-bluey-primary focus:ring-opacity-50"
                    >
                        Notificaciones
                    </button>
                    --}}
            </nav>
        </div>

        <div class="w-full md:w-3/4 p-8">
            <div x-show="currentTab === 'personal'" class="space-y-6">
                <h2 class="text-2xl font-bold text-bluey-dark mb-6">Detalles Personales</h2>

                <form action="{{ route('profile.update-personal') }}" method="POST">
                    @csrf
                    @method('PATCH') {{-- O PUT, dependiendo de cómo manejes la actualización --}}

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-bluey-primary focus:ring-bluey-primary @error('name') border-red-500 @enderror">
                            @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Condición para mostrar el campo Apellido solo si NO es un usuario de Google --}}
                        @if ($user->provider !== 'google')
                        <div>
                            <label for="apellido" class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                            <input type="text" name="apellido" id="apellido" value="{{ old('apellido', $user->cliente->apellido ?? '') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-bluey-primary focus:ring-bluey-primary @error('apellido') border-red-500 @enderror">
                            @error('apellido')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif



                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-bluey-primary focus:ring-bluey-primary @error('email') border-red-500 @enderror">
                            @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        <div>
                            <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            {{-- Aquí usamos $user->cliente->telefono --}}
                            <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $user->cliente->telefono ?? '') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-bluey-primary focus:ring-bluey-primary @error('telefono') border-red-500 @enderror">
                            @error('telefono')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                            {{-- Aquí usamos $user->cliente->direccion --}}
                            <input type="text" name="direccion" id="direccion" value="{{ old('direccion', $user->cliente->direccion ?? '') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-bluey-primary focus:ring-bluey-primary @error('direccion') border-red-500 @enderror">
                            @error('direccion')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8">
                        <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-bluey-primary hover:bg-bluey-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bluey-primary transition-colors duration-200">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>

            <div x-show="currentTab === 'password'" class="space-y-6">
                <h2 class="text-2xl font-bold text-bluey-dark mb-6">Cambiar Contraseña</h2>

                {{-- Lógica condicional para usuarios con registro tradicional vs. social --}}
                @if ($user->provider === 'email' || $user->provider === null) {{-- Asumiendo 'email' es el provider por defecto o si es null --}}
                <form action="{{ route('profile.update-password') }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña Actual</label>
                        <input type="password" name="current_password" id="current_password" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-bluey-primary focus:ring-bluey-primary @error('current_password') border-red-500 @enderror">
                        @error('current_password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                        <input type="password" name="password" id="password" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-bluey-primary focus:ring-bluey-primary @error('password') border-red-500 @enderror">
                        @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nueva Contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-bluey-primary focus:ring-bluey-primary">
                    </div>

                    <div class="mt-8">
                        <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-bluey-primary hover:bg-bluey-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bluey-primary transition-colors duration-200">
                            Cambiar Contraseña
                        </button>
                    </div>
                </form>
                @else
                {{-- Mensaje para usuarios de inicio de sesión social --}}
                <div class="bg-bluey-light2 border border-bluey-primary text-bluey-dark px-4 py-3 rounded relative" role="alert">
                    <p class="font-bold text-lg mb-2">Cuenta vinculada a {{ ucfirst($user->provider) }}</p>
                    <p class="text-base">Tu cuenta está asociada al inicio de sesión con {{ ucfirst($user->provider) }}.</p>
                    <p class="text-base mt-2">Para cambiar tu contraseña, por favor, gestiona la seguridad directamente desde la configuración de tu cuenta de <b>{{ ucfirst($user->provider) }}</b>.</p>
                    @if ($user->provider === 'google')
                    <p class="text-sm mt-3">Puedes ir a la página de seguridad de Google aquí: <a href="https://myaccount.google.com/security" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">myaccount.google.com/security</a></p>
                    @endif
                    {{-- Puedes añadir enlaces similares para otros proveedores si los usas --}}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection