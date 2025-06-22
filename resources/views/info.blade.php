<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Vet Dashboard</title>
  
  @vite('resources/css/vet/views/info.css')
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
  @vite('resources/css/vet/views/seccionesactivas.css') 
  @vite(['resources/css/Vet/panel.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  


  
</head>
<body>
  <div class="container">
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

    <!-- Main content -->
    <main class="main">
      <div class="header">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="btn-primary-logout" type="submit">Cerrar sesión</button>
        </form>
      </div>

      <div class="container">
  <h1 class="section-title">Perfil del Veterinario</h1>


  <div class="center-container">

    <div class="profile-card">
        <h2>{{ $veterinarian->user->name ?? 'No registrado' }}</h2>
        <p><strong>Correo:</strong> {{ $veterinarian->user->email ?? 'No registrado' }}</p>
        <p><strong>Teléfono:</strong> {{ $veterinarian->phone ?? 'No registrado' }}</p>
        <p><strong>Dirección:</strong> {{ $veterinarian->address ?? 'No registrada' }}</p>
        <p><strong>Licencia:</strong> {{ $veterinarian->license_number ?? 'No registrada' }}</p>
        <p><strong>Especialidad:</strong> {{ $veterinarian->specialty ?? 'No registrada' }}</p>
        <p><strong>Biografía:</strong></p>
        <p>{{ $veterinarian->bio ?? 'No registrada' }}</p>

        <a href="{{ route('index') }}" class="btn btn-primary mt-3">
  Editar Perfil
</a>
      </div>
    </div>
</div>

      
    </main>
  </div>
</body>
</html>




