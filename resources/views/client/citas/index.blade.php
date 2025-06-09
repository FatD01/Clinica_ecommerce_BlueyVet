{{-- resources/views/client/citas/index.blade.php --}}

@extends('layouts.app') {{-- ¡Asegúrate de que 'layouts.app' sea la ruta correcta a tu app.blade.php! --}}

@section('title', 'Registro de Citas') {{-- Esto actualiza el <title> en app.blade.php --}}

{{-- Puedes añadir CSS específico para esta página si no lo has importado en app.css --}}
{{-- @push('css')
    <link rel="stylesheet" href="{{ asset('path/to/specific-citas-style.css') }}">
@endpush --}}

@section('content') {{-- Esto inyecta el contenido en el @yield('content') de app.blade.php --}}
    <header>
        <div class="logo">
            <img src="#" alt="BlueyVet Logo">
            <h1>BlueyVet</h1>
        </div>
        <p>Sistema de Registro de Citas</p>
    </header>

    <div class="container">
        <h2 class="form-title">Registrar Nueva Cita</h2>

        <form id="formulario-cita">
            <div class="form-group">
                <label for="nombre-mascota">Nombre de la Mascota</label>
                <input type="text" class="form-control" id="nombre-mascota" placeholder="Ingresa el nombre de tu mascota" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tipo-mascota">Tipo de Mascota</label>
                    <select class="form-control select-control" id="tipo-mascota" required>
                        <option value="">Selecciona</option>
                        <option value="Perro">Perro</option>
                        <option value="Gato">Gato</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="raza-mascota">Raza</label>
                    <input type="text" class="form-control" id="raza-mascota" placeholder="Raza de tu mascota">
                </div>
            </div>

            <div class="form-group">
                <label for="tipo-servicio">Tipo de Servicio</label>
                <select class="form-control select-control" id="tipo-servicio" required>
                    <option value="">Selecciona un servicio</option>
                    <option value="Consulta General">Consulta General</option>
                    <option value="Vacunación">Vacunación</option>
                    <option value="Control de Rutina">Control de Rutina</option>
                    <option value="Desparasitación">Desparasitación</option>
                    <option value="Urgencia">Urgencia</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fecha-cita">Fecha</label>
                    <input type="date" class="form-control" id="fecha-cita" required>
                </div>

                <div class="form-group">
                    <label for="hora-cita">Hora</label>
                    <input type="time" class="form-control" id="hora-cita" required>
                </div>
            </div>

            <div class="form-group">
                <label for="veterinario">Veterinario</label>
                <select class="form-control select-control" id="veterinario" required>
                    <option value="">Selecciona un veterinario</option>
                    <option value="Dr. Ricardo Pérez">Dr. Ricardo Pérez (General)</option>
                    <option value="Dra. María Jiménez">Dra. María Jiménez (Felinos)</option>
                    <option value="Dr. Juan López">Dr. Juan López (General)</option>
                    <option value="Dra. Ana Martínez">Dra. Ana Martínez (Emergencias)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="notas">Notas adicionales (opcional)</label>
                <textarea class="form-control" id="notas" rows="3" placeholder="Describe brevemente el motivo de la consulta..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-calendar-check"></i> Registrar Cita
            </button>
        </form>

        <div class="citas-guardadas">
            <h3 class="form-title">Mis Citas</h3>
            <div id="lista-citas">
                <p class="no-citas">No tienes citas registradas.</p>
            </div>
        </div>
    </div>

    <div class="modal" id="modal-confirmacion">
        <div class="modal-content">
            <h3 class="modal-title">¡Cita Registrada!</h3>
            <div class="modal-body">
                <p>Tu cita ha sido registrada exitosamente.</p>
                <p>Recibirás una confirmación por correo electrónico.</p>
            </div>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-primary" id="btn-aceptar">Aceptar</button>
            </div>
        </div>
    </div>
@endsection

@push('js')
    {{-- Aquí se cargarán los scripts específicos para esta vista. --}}
    {{-- Para que citas.js sea procesado por Vite, necesitas importarlo en resources/js/app.js o un archivo js similar. --}}
    {{-- Por ejemplo, en resources/js/app.js podrías tener: import '../css/client/citas.js'; --}}
    <script src="{{ asset('assets/JS/citas.js') }}"></script> {{-- Si no lo vas a procesar con Vite, déjalo así --}}
@endpush