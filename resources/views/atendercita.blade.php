<!DOCTYPE html>
<html>

<head>
    <title>Atender Cita</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    @vite('resources/css/vet/views/atendercita.css')
    @vite('resources/css/vet/views/seccionesactivas.css')
    @vite(['resources/css/Vet/panel.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="brand">
            <i class="fas fa-paw"></i> BlueyVet
        </div>
        <nav>
            <ul>
                <li>
                    <a href="{{ route('veterinarian.citas') }}"
                        class="{{ request()->routeIs('veterinarian.citas') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt"></i> Consultar Citas
                    </a>
                </li>
                <li>
                    <a href="{{ route('historialmedico.index') }}"
                        class="{{ request()->routeIs('historialmedico.index') ? 'active' : '' }}">
                        <i class="fas fa-file-medical-alt"></i> Historial Médico
                    </a>
                </li>
                <li>
                    <a href="{{ route('veterinarian.profile') }}"
                        class="{{ request()->routeIs('veterinarian.profile*') || request()->routeIs('veterinarian.edit') ? 'active' : '' }}">
                        <i class="fas fa-user"></i> Mi Información
                    </a>
                </li>
                <li>
                    <a href="{{ route('datosestadisticos') }}" class="{{ request()->routeIs('datosestadisticos') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i> Datos estadísticos
                    </a>
                </li>
                <li>
                    <a href="{{ route('veterinarian.notificaciones') }}"
                        class="{{ request()->routeIs('veterinarian.notificaciones') ? 'active' : '' }}">
                        <i class="fas fa-bell"></i> Notificaciones
                        @if($unreadCount)
                        <span class="notification-dot"></span>
                        @endif
                    </a>
                </li>
            </ul>
        </nav>
        <div class="user">
            Hola, <strong>{{ Auth::user()->name }}</strong>
        </div>
    </aside>

    <div class="main-content container mt-4">
        <h2 class="mb-4">Formulario de atención médica</h2>

        <div class="card mb-3">
            <div class="card-header">Información del Cliente</div>
            <div class="card-body">
                <p><strong>Nombre:</strong> {{ $cliente->nombre }} {{ $cliente->apellido }}</p>
                <p><strong>Email:</strong> {{ $usuario->email ?? 'No disponible' }}</p>
                <p><strong>Teléfono:</strong> {{ $cliente->telefono ?? 'No registrado' }}</p>
                <p><strong>Dirección:</strong> {{ $cliente->direccion ?? 'No registrada' }}</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Información de la Mascota</div>
            <div class="card-body">
                <p><strong>Nombre:</strong> {{ $mascota->name }}</p>
                <p><strong>Especie:</strong> {{ $mascota->species }}</p>
                <p><strong>Raza:</strong> {{ $mascota->race }}</p>
                <p><strong>Peso:</strong> {{ $mascota->weight }} kg</p>
                <p><strong>Nacimiento:</strong> {{ $mascota->birth_date }}</p>
                <p><strong>Alergias:</strong> {{ $mascota->allergies }}</p>
                @if($mascota->image)
                <img src="{{ asset('storage/' . $mascota->image) }}" alt="Imagen" class="img-fluid" style="max-width: 200px;">
                @endif
                <hr>
                <p><strong>Razón de la cita:</strong> {{ $appointment->reason }}</p>
                <p><strong>Fecha de la cita:</strong> {{ $appointment->date }}</p>
            </div>
        </div>

    

        <form method="POST" action="{{ route('veterinarian.guardar.atencion') }}">

            @csrf
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">


            <div class="mb-3">
                <label for="diagnosis" class="form-label">Diagnóstico</label>
                <textarea name="diagnosis" class="form-control" rows="2">{{ old('diagnosis') }}</textarea>
            </div>

            <div class="mb-3">
                <label for="treatment" class="form-label">Tratamiento</label>
                <textarea name="treatment" class="form-control" rows="2">{{ old('treatment') }}</textarea>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notas</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            <div class="mb-3">
                <label for="prescription" class="form-label">Receta médica</label>
                <textarea name="prescription" class="form-control" rows="2">{{ old('prescription') }}</textarea>
            </div>

            <div class="mb-3">
                <label for="observations" class="form-label">Observaciones</label>
                <textarea name="observations" class="form-control" rows="2">{{ old('observations') }}</textarea>
            </div>

            <hr class="my-4">
            <div class="card mb-3">
                <div class="card-header">Programar Recordatorio (Opcional)</div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="create_reminder_checkbox" name="create_reminder" value="1" {{ old('create_reminder') ? 'checked' : '' }}>
                        <label class="form-check-label" for="create_reminder_checkbox">
                            **Activar recordatorio para esta atención**
                        </label>
                    </div>

                    <div id="reminder_fields" style="display: {{ old('create_reminder') ? 'block' : 'none' }};">
                        <div class="mb-3">
                            <label for="reminder_title" class="form-label">Título del Recordatorio</label>
                            <input type="text" name="reminder_title" id="reminder_title" class="form-control" value="{{ old('reminder_title') }}" placeholder="Ej: Vacuna antirrábica, Desparasitación, Cita de revisión">
                            @error('reminder_title') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="reminder_description" class="form-label">Descripción del Recordatorio</label>
                            <textarea name="reminder_description" id="reminder_description" class="form-control" rows="2" placeholder="Detalles adicionales sobre el recordatorio (opcional)">{{ old('reminder_description') }}</textarea>
                            @error('reminder_description') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="remind_at_date" class="form-label">Fecha del Recordatorio</label>
                                <input type="date" name="remind_at_date" id="remind_at_date" class="form-control" value="{{ old('remind_at_date') }}">
                                @error('remind_at_date') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="remind_at_time" class="form-label">Hora del Recordatorio</label>
                                <input type="time" name="remind_at_time" id="remind_at_time" class="form-control" value="{{ old('remind_at_time') }}">
                                @error('remind_at_time') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success">Enviar</button>
            <a href="{{ route('veterinarian.citas') }}" class="btn btn-secondary">← Cancelar</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('create_reminder_checkbox');
        const reminderFields = document.getElementById('reminder_fields');

        // Función para alternar la visibilidad
        function toggleReminderFields() {
            if (checkbox.checked) {
                reminderFields.style.display = 'block';
            } else {
                reminderFields.style.display = 'none';
            }
        }

        // Llama a la función al cargar la página para reflejar el estado inicial (old('create_reminder'))
        toggleReminderFields();

        // Añade el evento para cuando cambia el checkbox
        checkbox.addEventListener('change', toggleReminderFields);
    });
</script>
</body>

</html>