<x-filament::page>
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Calendario de Horarios</h2>
        <div wire:ignore id="calendar"></div>
    </div>

    <div class="mb-6">
        {{ $this->createAction }}
    </div>

    {{ $this->table }}

    {{-- FullCalendar Scripts --}}
    <!-- <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales-all.min.js'></script> -->
    <!-- <script src=" https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js "></script> -->
    
    <!-- <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales-all.min.js"></script> -->

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales-all.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.17/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.17/index.global.min.js'></script>

    
    <script>
        let calendar;

        function renderVeterinarianCalendar(events) {
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;

            if (calendar) calendar.destroy();

            calendar = new FullCalendar.Calendar(calendarEl, {
                editable: false,
                selectable: false,
                initialView: 'dayGridMonth',
                locale: 'es',
                height: 600,
                with:1000,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                events: events,
            });

            calendar.render();
        }

        document.addEventListener("DOMContentLoaded", function () {
            renderVeterinarianCalendar(@json($calendarEvents));
        });

        document.addEventListener("livewire:load", function () {
            renderVeterinarianCalendar(@json($calendarEvents));
        });

        // 🚀 Recargar al cerrar modal con X o clic fuera
        // document.addEventListener("close-modal", function () {
        //     window.location.reload();
        // });

        // 🚀 Recargar también si se presiona el botón Cancelar
        // document.addEventListener('click', function (e) {
        //     const cancelButton = e.target.closest('button');
        //     if (!cancelButton) return;

        //     if (
        //         cancelButton.innerText.trim().toLowerCase() === 'cancelar' ||
        //         cancelButton.innerText.trim().toLowerCase() === 'cancel'
        //     ) {
        //         // Espera unos milisegundos para que cierre el modal, luego recarga
        //         setTimeout(() => {
        //             window.location.reload();
        //         }, 300);
        //     }
        // });
    </script>
</x-filament::page>