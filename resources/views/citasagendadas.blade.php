<!DOCTYPE html>
<html>
<head>
    <title>Citas agendadas</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales-all.global.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">

    @vite(['resources/css/vet/views/filtros.css'])
    @vite(['resources/css/Vet/views/citasagendadas.css'])
    @vite(['resources/css/Vet/views/seccionesactivas.css'])
    @vite(['resources/css/Vet/views/calendario.css'])
    @vite(['resources/css/Vet/panel.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">




</head>
<body>
<div class="layout">
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
        <h2 class="mb-4">Citas Agendadas</h2>

        <form method="GET" action="{{ route('veterinarian.citas') }}" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="mascota_id" class="form-label">Filtrar por mascota</label>
                    <select name="mascota_id" id="mascota_id" class="form-select">
                        <option value="">-- Todas las mascotas --</option>
                        @foreach ($mascotas as $mascota)
                            <option value="{{ $mascota->id }}" {{ request('mascota_id') == $mascota->id ? 'selected' : '' }}>
                                {{ $mascota->name }}({{ $mascota->species }} - {{ $mascota->race }}) - {{ $mascota->cliente->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

             

                <div class="col-md-2">
                    <button type="submit" class="btn mi-boton-personalizado">Aplicar filtro</button>

                </div>
            </div>
        </form>

        <div class="mb-3">
    <button type="button" class="btn boton-ver-cita" id="verReciente">Ver cita más reciente</button>
</div>



        @if (!is_null($mascotaId))
    @php
        $mascotaSeleccionada = $mascotas->firstWhere('id', (int)$mascotaId);
    @endphp

    @if ($numeroCitasMascota > 0)
        <div class="alert alert-success mt-2">
            La mascota&nbsp;<strong> {{ $mascotaSeleccionada->name }} </strong>&nbsp;tiene {{ $numeroCitasMascota }} cita{{ $numeroCitasMascota > 1 ? 's' : '' }} agendada{{ $numeroCitasMascota > 1 ? 's' : '' }}.
        </div>
    @else
        <div class="alert alert-warning mt-2">
            La mascota&nbsp;<strong>{{ $mascotaSeleccionada->name }}</strong> &nbsp;no tiene citas agendadas.
        </div>
    @endif
@endif


        <div id="calendarContainer">
            <div id="calendar" class="mb-4 p-3 bg-white rounded shadow"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales-all.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            // ÚNICO CAMBIO: Quitar 'listWeek'
            right: 'dayGridMonth,dayGridWeek,timeGridDay'
        },

        buttonText: {
        today: 'Hoy',
        month: 'Mes',
        week: 'Semana',
        day: 'Día'
    },
        events: @json($eventos),
        eventClick: function(info) {
    info.jsEvent.preventDefault();
    
    const data = info.event.extendedProps;
    const fechaEvento = new Date(info.event.start);
    const ahora = new Date();

    const fechaEventoSimple = fechaEvento.toISOString().slice(0, 10);
    const ahoraSimple = ahora.toISOString().slice(0, 10);

    if (fechaEventoSimple < ahoraSimple) {
        // Modal para citas pasadas
        const modalPasada = `
    <div class="modal fade" id="citaPasadaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3 border-warning">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i> Cita No Atendida
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-0">
                        <p><strong>Esta cita ya pasó</strong> y no puede ser modificada ni atendida.</p>
                        <p><strong>Fecha de la cita:</strong> ${fechaEvento.toLocaleDateString()}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
`;
        document.body.insertAdjacentHTML('beforeend', modalPasada);
        const modal = new bootstrap.Modal(document.getElementById('citaPasadaModal'));
        modal.show();
        document.getElementById('citaPasadaModal').addEventListener('hidden.bs.modal', function () {
            this.remove();
        });

    } else {
        // Modal para citas vigentes o futuras
        const modalHtml = `
            <div class="modal fade" id="detalleCitaModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content p-3">
                        <div class="modal-header">
                            <h5 class="modal-title">Detalle de la Cita</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Cliente:</strong> ${data.cliente}</p>
                            <p><strong>Email:</strong> ${data.email}</p>
                            <p><strong>Teléfono:</strong> ${data.telefono}</p>
                            <p><strong>Dirección:</strong> ${data.direccion}</p>
                            <p><strong>Servicio:</strong> ${data.servicio}</p>
                            <a href="${data.verMascotasUrl}" class="btn btn-primary mt-2">Ver mascotas</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('detalleCitaModal'));
        modal.show();
        document.getElementById('detalleCitaModal').addEventListener('hidden.bs.modal', function () {
            this.remove();
        });
    }
},
        views: {
            // Mantener dayGridWeek tal como estaba en tu código original
            dayGridWeek: {
                dayHeaderContent: function(arg) {
                    const weekday = arg.date.toLocaleDateString('es', { weekday: 'short' }).toUpperCase();
                    const dayNum = arg.date.getDate();
                    return { html: `<span class="fc-col-header-cell-dayname">${weekday}</span><span class="fc-daygrid-day-number">${dayNum}</span>` };
                }
            },
            // Mantener timeGridDay tal como estaba en tu código original
            timeGridDay: {
                dayHeaderContent: function(arg) {
                    const weekday = arg.date.toLocaleDateString('es', { weekday: 'short' }).toUpperCase();
                    const dayNum = arg.date.getDate();
                    return { html: `<span class="fc-timegrid-col-dayname">${weekday}</span><span class="fc-timegrid-col-daynum">${dayNum}</span>` };
                },
                slotMinTime: '00:00:00',
                slotMaxTime: '24:00:00',
                slotLabelFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    omitZeroMinute: true,
                    meridiem: 'short'
                }
            },
            dayGridMonth: {
                // No se necesita dayHeaderContent aquí, FullCalendar ya lo maneja bien.
                // Tu CSS ya apunta a .fc-daygrid-day-number para el círculo amarillo.
            }
        }
    });
    calendar.render();

    document.getElementById('verReciente').addEventListener('click', function () {
    const now = new Date();
    let eventos = calendar.getEvents();

    // Filtra eventos futuros
    eventos = eventos.filter(ev => new Date(ev.start) >= now);

    // Encuentra el más próximo
    eventos.sort((a, b) => new Date(a.start) - new Date(b.start));
    const proximo = eventos[0];

    if (proximo) {
        // Navegar a la fecha
        calendar.gotoDate(proximo.start);

        // Esperar a que el DOM se actualice, luego resaltar
        setTimeout(() => {
            const celda = document.querySelector(`[data-date='${proximo.startStr.slice(0, 10)}']`);
            if (celda) {
                celda.classList.add('evento-resaltado');
                setTimeout(() => celda.classList.remove('evento-resaltado'), 4000);
            }
        }, 300);
    } else {
        alert('No hay citas futuras.');
    }
});

});
</script>
</body>
</html>