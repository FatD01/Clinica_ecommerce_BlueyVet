<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Vet Dashboard</title>



  @vite(['resources/css/vet/views/styles.css'])
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
  @vite(['resources/css/vet/views/seccionesactivas.css'])
  @vite(['resources/css/Vet/panel.css'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <style>
    /* Estilo general del cuerpo: Elimina overflow: hidden para permitir scroll */
    html,
    body {
      margin: 0;
      padding: 0;
      height: 100%;
      /* Quitado: overflow: hidden; */
    }



    /* Asegúrate de que tu main content pueda hacer scroll si es necesario */
    .main {
      flex-grow: 1;
      /* Permite que ocupe el espacio restante */
      overflow-y: auto;
      /* Permite scroll vertical si el contenido excede la altura */
      padding-bottom: 2rem;
      /* Espacio para el final del scroll */
    }

    /* Estilo general de la sección de edición */
    .profile-edit-section {
      background-color: #fff;
      padding: 2rem;
      margin: 2rem auto;
      border-radius: 1rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      max-width: 900px;
      /* Limita el ancho para un mejor diseño */
      width: 90%;
      /* Ajuste de ancho responsivo */
    }

    /* Título de sección */
    .section-title {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      color: #1e3a8a;
      text-align: center;
    }

    /* Diseño en cuadrícula */
    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-bottom: 1.5rem;
    }

    /* Grupos de formularios */
    .form-group {
      display: flex;
      flex-direction: column;
      position: relative;
      /* Importante para el autocompletado */
    }

    .form-label {
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #444;
    }

    .form-input,
    .form-textarea {
      padding: 0.75rem;
      border-radius: 0.5rem;
      border: 1px solid #ccc;
      font-size: 1rem;
      transition: border 0.3s ease;
      width: 100%;
      /* Asegura que ocupen todo el ancho disponible */
    }

    .form-input:focus,
    .form-textarea:focus {
      border-color: #3498db;
      outline: none;
    }

    /* Área de texto */
    .form-textarea {
      min-height: 120px;
      resize: vertical;
    }

    /* Errores */
    .input-error {
      border-color: #e74c3c;
    }

    .error-message {
      color: #e74c3c;
      font-size: 0.9rem;
      margin-top: 0.25rem;
    }

    /* Botones */
    .logout-wrapper {
      background: none;
      padding: 1.5rem 2rem 0 2rem;
      text-align: right;
    }

    .form-buttons {
      display: flex;
      justify-content: flex-end;
      gap: 1rem;
      margin-top: 2rem;
    }

    .btn-primary-custom,
    .btn-secondary-custom {
      padding: 0.75rem 1.5rem;
      border-radius: 0.5rem;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.3s ease;
      text-align: center;
      border: none;
      /* Asegura que no haya bordes por defecto */
    }

    .btn-primary-custom {
      background-color: #1e3a8a;
      color: #fff;
    }

    .btn-primary-custom:hover {
      background-color: #2980b9;
    }

    .btn-secondary-custom {
      background-color: #ecf0f1;
      color: #2c3e50;
    }

    .btn-secondary-custom:hover {
      background-color: #d0d7de;
    }

    /* Alertas */
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
      padding: 1rem;
      border-radius: 0.5rem;
      margin-bottom: 1.5rem;
      text-align: center;
    }

    /* Estilos para la lista de sugerencias y los badges de especialidades */
    /* La clase position-relative ya la tiene el form-group */
    #specialty-suggestions {
      background-color: white;
      border: 1px solid #ddd;
      border-radius: 0.5rem;
      /* Ajustado para que coincida con otros bordes redondeados */
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      /* Sombra más suave */
      list-style: none;
      padding: 0;
      margin-top: 0.25rem;
      /* Pequeño margen superior */
      position: absolute;
      z-index: 1000;
      top: calc(100% + 5px);
      /* Posicionar justo debajo del input con un pequeño espacio */
      left: 0;
      right: 0;
      width: 100%;
      max-height: 200px;
      overflow-y: auto;
      border-top: none;
      /* Quita el borde superior para que se vea más conectado al input */
    }

    #specialty-suggestions .list-group-item {
      cursor: pointer;
      padding: 0.75rem 1rem;
      font-size: 1rem;
      /* Asegura que el tamaño de fuente sea consistente */
    }

    #specialty-suggestions .list-group-item:hover {
      background-color: #f0f0f0;
      /* Color de hover más suave */
    }

    .selected-specialties-container {
      /* Nuevo nombre de clase para evitar conflicto con Bootstrap .container */
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      /* Espacio entre los badges */
      margin-top: 0.75rem;
      /* Margen superior para separarlo del input */
      padding: 0.5rem 0;
      /* Padding para que los badges no toquen los bordes */
    }

    .badge.specialty-badge {
      /* Clase específica para los badges de especialidad */
      background-color: #1e3a8a;
      /* Color de tu botón primario */
      color: #fff;
      padding: 0.6em 0.9em;
      border-radius: 0.5rem;
      display: inline-flex;
      align-items: center;
      font-size: 0.95em;
      font-weight: 500;
    }

    .badge.specialty-badge .btn-close {
      font-size: 0.6em;
      margin-left: 0.75em;
      /* Más espacio para la X */
      line-height: 1;
      filter: invert(1);
      /* Para hacer la X blanca en fondo oscuro */
      opacity: 0.8;
    }

    .badge.specialty-badge .btn-close:hover {
      opacity: 1;
    }

    .notification-dot {
      height: 10px;
      width: 10px;
      background-color: #ffc107;
      /* Color amarillo */
      border-radius: 50%;
      display: inline-block;
      margin-left: 5px;
      vertical-align: middle;
    }
  </style>

</head>

<body>
  <div class="container-fluid">
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
              @if(isset($unreadCount) && $unreadCount)
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

    <main class="main">
      <div class="logout-wrapper">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="btn-primary-logout" type="submit">Cerrar sesión</button>
        </form>
      </div>
      <div class="profile-edit-section">
        <h1 class="section-title">Editar Perfil de Veterinario</h1>

        @if (session('success'))
        <div class="alert-success" role="alert">
          {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('veterinarian.profile.update') }}" method="POST">
          @csrf
          @method('PATCH')

          <div class="form-grid">
            <div class="form-group">
              <label for="name" class="form-label">Nombre Completo:</label>
              <input type="text" id="name" name="name"
                class="form-input @error('name') input-error @enderror"
                value="{{ old('name', $veterinarian->user->name ?? '') }}"
                placeholder="Tu nombre completo">
              @error('name')
              <p class="error-message">{{ $message }}</p>
              @enderror
            </div>

            <div class="form-group">
              <label for="email" class="form-label">Correo Electrónico:</label>
              <input type="email" id="email" name="email"
                class="form-input @error('email') input-error @enderror"
                value="{{ old('email', $veterinarian->user->email ?? '') }}"
                placeholder="tu.correo@example.com">
              @error('email')
              <p class="error-message">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="form-grid mt-4">
            <div class="form-group">
              <label for="password" class="form-label">Nueva Contraseña:</label>
              <input type="password" name="password" id="password"
                class="form-input @error('password') input-error @enderror"
                {{ $user->hasPasswordChanged ? 'disabled' : '' }} {{-- Deshabilita si ya fue cambiada --}}
                placeholder="Deja vacío para no cambiar">
              @error('password')
              <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
              @enderror
            </div>

            <div class="form-group">
              <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña:</label>
              <input type="password" name="password_confirmation" id="password_confirmation"
                class="form-input"
                {{ $user->hasPasswordChanged ? 'disabled' : '' }} {{-- Deshabilita si ya fue cambiada --}}
                placeholder="Repite tu nueva contraseña">
            </div>
          </div>
          @if ($user->hasPasswordChanged)
          <p class="text-red-600 text-sm mt-3 text-center">
            * La contraseña ya fue cambiada una vez. Para futuras modificaciones, contacta al área de administración.
          </p>
          @endif

          <div class="form-grid">
            <div class="form-group">
              <label for="license_number" class="form-label">Número de Licencia:</label>
              <input type="text" id="license_number" name="license_number"
                class="form-input @error('license_number') input-error @enderror"
                value="{{ old('license_number', $veterinarian->license_number ?? '') }}"
                placeholder="Ej: 12345"
                maxlength="5">
              @error('license_number')
              <p class="error-message">{{ $message }}</p>
              @enderror
            </div> {{-- CAMBIO CRUCIAL: Campo de especialidades con autocompletado --}}
            <div class="form-group"> {{-- 'position-relative' se añade aquí en el CSS directamente para .form-group --}}
              <label for="specialties-input" class="form-label">Especialidades:</label>
              <input type="text" id="specialties-input"
                class="form-input @error('specialties') input-error @enderror" {{-- Cambiado 'specialty' a 'specialties' --}}
                placeholder="Empieza a escribir una especialidad..." />

              <div id="selected-specialties-container" class="selected-specialties-container">
                {{-- Las especialidades existentes se cargarán aquí y se añadirán nuevas --}}
                {{-- Verifica que $veterinarian existe antes de intentar acceder a sus propiedades --}}
                @if($veterinarian && $veterinarian->specialties->isNotEmpty()) {{-- Aquí la clave: specialties es una colección --}}
                @foreach($veterinarian->specialties as $specialty) {{-- Itera sobre la colección de objetos Specialty --}}
                <span class="badge specialty-badge">
                  {{ $specialty->name }} {{-- Accede al nombre de la especialidad desde el objeto --}}
                  <button type="button" class="btn-close btn-close-white ms-1" aria-label="Remove" data-specialty="{{ $specialty->name }}"></button>
                </span>
                @endforeach
                @endif
              </div>

              {{-- El input hidden debe contener los nombres de las especialidades separados por coma --}}
              <input type="hidden" name="specialties" id="specialties-hidden-input" value="{{ old('specialties', $veterinarian->specialtyNames ?? '') }}"> {{-- Usamos el accessor specialtyNames --}}
              @error('specialties') {{-- Cambiado 'specialty' a 'specialties' --}}
              <p class="error-message">{{ $message }}</p>
              @enderror

              <ul id="specialty-suggestions" class="list-group" style="display: none;">
              </ul>
            </div>

            <div class="form-group">
              <label for="phone" class="form-label">Teléfono:</label>
              <input type="tel" id="phone" name="phone"
                class="form-input @error('phone') input-error @enderror"
                value="{{ old('phone', $veterinarian->phone ?? '') }}"
                placeholder="Ej: +51 987 654 321"
                maxlength="11"
                pattern="[0-9]{3} [0-9]{3} [0-9]{3}"
                inputmode="numeric">
              @error('phone')
              <p class="error-message">{{ $message }}</p>
              @enderror
            </div>

            <div class="form-group">
              <label for="address" class="form-label">Dirección:</label>
              <input type="text" id="address" name="address"
                class="form-input @error('address') input-error @enderror"
                value="{{ old('address', $veterinarian->address ?? '') }}"
                placeholder="Ej: Av. Principal 123, Ciudad">
              @error('address')
              <p class="error-message">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="form-group">
            <label for="bio" class="form-label">Biografía / Sobre Mí:</label>
            <textarea id="bio" name="bio"
              class="form-textarea @error('bio') input-error @enderror"
              placeholder="Describe tu experiencia, tu filosofía de trabajo, etc.">{{ old('bio', $veterinarian->bio ?? '') }}</textarea>
            @error('bio')
            <p class="error-message">{{ $message }}</p>
            @enderror
          </div>

          <div class="form-buttons">
            <a href="{{ route('index') }}" class="btn-secondary-custom">Cancelar</a>
            <button type="submit" class="btn-primary-custom">Guardar Cambios</button>
          </div>
        </form>
      </div>
    </main>
  </div>

  {{-- Script para Bootstrap (necesario para btn-close, etc.) --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const specialtiesInput = document.getElementById('specialties-input');
      const hiddenSpecialtiesInput = document.getElementById('specialties-hidden-input');
      const suggestionsList = document.getElementById('specialty-suggestions');
      const selectedSpecialtiesContainer = document.getElementById('selected-specialties-container');

      let selectedSpecialties = [];

      // Inicializar especialidades seleccionadas desde el input oculto
      if (hiddenSpecialtiesInput.value) {
        // Filtra cadenas vacías que puedan resultar de múltiples comas o comas al inicio/final
        selectedSpecialties = hiddenSpecialtiesInput.value.split(',').map(s => s.trim()).filter(s => s !== '');
      }

      // Función para renderizar las especialidades seleccionadas
      function renderSelectedSpecialties() {
        selectedSpecialtiesContainer.innerHTML = '';
        selectedSpecialties.forEach(specialty => {
          if (specialty) { // Asegura que no se añadan elementos vacíos
            const span = document.createElement('span');
            // Usamos la nueva clase specialty-badge
            span.className = 'badge specialty-badge';
            span.textContent = specialty;
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn-close btn-close-white ms-1';
            button.setAttribute('aria-label', 'Remove');
            button.dataset.specialty = specialty;
            span.appendChild(button);
            selectedSpecialtiesContainer.appendChild(span);
          }
        });
        hiddenSpecialtiesInput.value = selectedSpecialties.join(',');
      }

      renderSelectedSpecialties(); // Renderizar las especialidades iniciales

      // Listener de evento para eliminar especialidades seleccionadas
      selectedSpecialtiesContainer.addEventListener('click', function(event) {
        if (event.target.classList.contains('btn-close')) {
          const specialtyToRemove = event.target.dataset.specialty;
          selectedSpecialties = selectedSpecialties.filter(s => s !== specialtyToRemove);
          renderSelectedSpecialties();
        }
      });

      specialtiesInput.addEventListener('input', debounce(function() {
        const query = specialtiesInput.value.trim();
        if (query.length < 2) { // Empezar a buscar después de 2 caracteres
          suggestionsList.style.display = 'none';
          suggestionsList.innerHTML = '';
          return;
        }

        // Llamada AJAX para obtener especialidades
        fetch(`/veterinario/specialties?query=${encodeURIComponent(query)}`)
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
          })
          .then(data => {
            suggestionsList.innerHTML = '';
            if (data.length > 0) {
              data.forEach(specialty => {
                // Solo añadir sugerencias que no estén ya seleccionadas
                if (!selectedSpecialties.includes(specialty)) {
                  const li = document.createElement('li');
                  li.className = 'list-group-item list-group-item-action';
                  li.textContent = specialty;
                  li.addEventListener('click', function() {
                    selectedSpecialties.push(specialty);
                    renderSelectedSpecialties();
                    specialtiesInput.value = ''; // Limpiar input después de la selección
                    suggestionsList.style.display = 'none';
                  });
                  suggestionsList.appendChild(li);
                }
              });
              // Mostrar la lista solo si hay sugerencias nuevas
              if (suggestionsList.children.length > 0) {
                suggestionsList.style.display = 'block';
              } else {
                suggestionsList.style.display = 'none';
              }
            } else {
              suggestionsList.style.display = 'none';
            }
          })
          .catch(error => console.error('Error al obtener especialidades:', error));
      }, 700)); // Debounce para evitar demasiadas peticiones

      // Ocultar sugerencias al hacer clic fuera
      document.addEventListener('click', function(event) {
        if (!specialtiesInput.contains(event.target) && !suggestionsList.contains(event.target)) {
          suggestionsList.style.display = 'none';
        }
      });

      // Función debounce (utilidad estándar)
      function debounce(func, delay) {
        let timeout;
        return function(...args) {
          const context = this;
          clearTimeout(timeout);
          timeout = setTimeout(() => func.apply(context, args), delay);
        };
      }
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
        const phoneInput = document.getElementById('phone');

        phoneInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, ''); // Elimina todo lo que no sea dígito

            let formattedValue = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 3 === 0) {
                    formattedValue += ' '; // Añade un espacio cada 3 dígitos
                }
                formattedValue += value[i];
            }

            // Limitar a 9 dígitos (ignorando los espacios para el conteo)
            if (formattedValue.replace(/\s/g, '').length > 9) {
                formattedValue = formattedValue.substring(0, 11); // Corta la cadena formateada a 11 (9 digitos + 2 espacios)
                formattedValue = formattedValue.replace(/\s/g, '').substring(0, 9).replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3'); // Re-formatea por si el corte fue raro
            }

            e.target.value = formattedValue;
        });
    });
</script>
</body>

</html>