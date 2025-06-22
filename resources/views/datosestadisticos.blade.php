<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Veterinario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Estilos --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    @vite('resources/css/vet/views/seccionesactivas.css')
    @vite('resources/css/vet/views/datosestadisticos.css') 
    @vite('resources/css/vet/panel.css') 
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

 
</head>
<body>
<div class="container-fluid">
    {{-- Sidebar --}}
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
    {{-- Contenido principal --}}
    <div class="main-content">
        <a href="{{ route('index') }}" class="btn btn-outline-secondary mb-3">
            &#8592; Atrás
        </a>

       

        {{-- Filtro --}}
        <div class="mb-4">
            <label for="tipoGrafico" class="form-label">Selecciona el tipo de gráfico</label>
            <select id="tipoGrafico" class="form-select" style="max-width: 300px">
                <option value="citas">📅 Citas atendidas</option>
                <option value="servicios">🩺 Servicios más solicitados</option>
            </select>
        </div>

        {{-- Estadísticas --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <h5>Citas atendidas</h5>
                        <h2>{{ $totalCompletadas }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body text-center">
                        <h5>Citas pendientes</h5>
                        <h2>{{ $totalPendientes }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-danger">
                    <div class="card-body text-center">
                        <h5>Citas canceladas</h5>
                        <h2>{{ $totalCanceladas }}</h2>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfico de citas --}}
        <div id="graficoCitas" class="card mb-4">
            <div class="card-header">📅 Citas atendidas en los últimos 7 días</div>
            <div class="card-body">
                @if ($data->sum() === 0)
                    <p class="text-muted text-center">No has atendido citas esta semana.</p>
                @else
                    <canvas id="chartCitas" height="100"></canvas>
                @endif
            </div>
        </div>

        {{-- Gráfico de servicios --}}
        <div id="graficoServicios" class="card mb-4 d-none">
            <div class="card-header">🩺 Servicios más solicitados</div>
            <div class="card-body">
                @if ($servicios->isEmpty())
                    <p class="text-muted text-center">Aún no has registrado servicios.</p>
                @else
                    <canvas id="chartServicios" height="100"></canvas>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const tipoGrafico = document.getElementById('tipoGrafico');
    const graficoCitas = document.getElementById('graficoCitas');
    const graficoServicios = document.getElementById('graficoServicios');

    tipoGrafico.addEventListener('change', () => {
        if (tipoGrafico.value === 'citas') {
            graficoCitas.classList.remove('d-none');
            graficoServicios.classList.add('d-none');
        } else {
            graficoCitas.classList.add('d-none');
            graficoServicios.classList.remove('d-none');
        }
    });

    const labelsCitas = @json($labels);
    const dataCitas = @json($data);
    const labelsServicios = @json($servicios->pluck('nombre'));
    const dataServicios = @json($servicios->pluck('total'));
</script>

@if ($data->sum() > 0)
<script>
    new Chart(document.getElementById('chartCitas').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labelsCitas,
            datasets: [{
                label: 'Citas atendidas',
                data: dataCitas,
                backgroundColor: '#0d6efd',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
</script>
@endif

@if ($servicios->isNotEmpty())
<script>
    new Chart(document.getElementById('chartServicios').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labelsServicios,
            datasets: [{
                label: 'Cantidad',
                data: dataServicios,
                backgroundColor: '#20c997',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endif

</body>
</html>
