<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Vet Dashboard</title>
  
  <link rel="stylesheet" href="{{ asset('info.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('seccionesactivas.css') }}">


  
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




