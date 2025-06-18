<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Vet Dashboard</title>
  
  <link rel="stylesheet" href="{{ asset('styles.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('seccionesactivas.css') }}">

<style>
        /* Estilo general de la sección */
.profile-edit-section {
    background-color: #fff;
    padding: 2rem;
    margin: 2rem auto;
    border-radius: 1rem;
    max-width: 600px;
    max-height: 450px; /* altura limitada */
    overflow-y: auto;  /* scroll vertical si se necesita */
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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

.btn-primary-logout{
    background-color:rgb(12, 78, 177);
    color: #fff;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease; 
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
}

.btn-primary-custom {
    background-color: #1e3a8a;
    color: #fff;
    border: none;
}

.btn-primary-custom:hover {
    background-color: #2980b9;
}

.btn-secondary-custom {
    background-color: #ecf0f1;
    color: #2c3e50;
    border: none;
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

    </style>
  
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="brand">BlueyVet</div>
      <nav>
    <ul>
        <li>
            <a href="{{ route('veterinarian.citas') }}"
               class="{{ request()->routeIs('veterinarian.citas') ? 'active' : '' }}">
               Consultar Citas
            </a>
        </li>
        <li>
  <a href="{{ route('historialmedico.index') }}"
     class="{{ request()->routeIs('historialmedico.index') ? 'active' : '' }}">
     Historial Médico
  </a>
</li>

        
        <li>
            <a href="{{ route('veterinarian.profile') }}"
   class="{{ request()->routeIs('veterinarian.profile*') || request()->routeIs('veterinarian.edit') ? 'active' : '' }}">
   Mi Información
</a>

        </li>
    </ul>
</nav>

      <div class="user">Hola, <strong>{{ Auth::user()->name }}</strong></div>
    </aside>

    <!-- Main content -->
    <main class="main">
      <div class="header">
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

          <!-- Datos del Usuario -->
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

          <!-- Datos del Veterinario -->
          <div class="form-grid">
            <div class="form-group">
              <label for="license_number" class="form-label">Número de Licencia:</label>
              <input type="text" id="license_number" name="license_number"
                     class="form-input @error('license_number') input-error @enderror"
                     value="{{ old('license_number', $veterinarian->license_number ?? '') }}"
                     placeholder="Ej: AB12345">
              @error('license_number')
                <p class="error-message">{{ $message }}</p>
              @enderror
            </div>

            <div class="form-group">
              <label for="specialty" class="form-label">Especialidad:</label>
              <input type="text" id="specialty" name="specialty"
                     class="form-input @error('specialty') input-error @enderror"
                     value="{{ old('specialty', $veterinarian->specialty ?? '') }}"
                     placeholder="Ej: Medicina Interna, Cirugía">
              @error('specialty')
                <p class="error-message">{{ $message }}</p>
              @enderror
            </div>

            <div class="form-group">
              <label for="phone" class="form-label">Teléfono:</label>
              <input type="tel" id="phone" name="phone"
                     class="form-input @error('phone') input-error @enderror"
                     value="{{ old('phone', $veterinarian->phone ?? '') }}"
                     placeholder="Ej: +51 987 654 321">
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
</body>
</html>


