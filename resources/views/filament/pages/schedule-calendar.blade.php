<x-filament-pages::page>
    <div
        x-data="{
            calendar: null,
            events: @entangle('events'), // Asegúrate de que 'events' esté entrelazado
            resources: @entangle('resources'), // ¡NUEVO! Entrelaza la propiedad 'resources'
            init() {
                const Calendar = FullCalendar.Calendar;
                const DayGrid = FullCalendar.DayGrid;
                const TimeGrid = FullCalendar.TimeGrid;
                const ListPlugin = FullCalendar.List;
                const InteractionPlugin = FullCalendar.Interaction;
                // Si quieres usar vista de recursos (timeline o vertical), necesitas un plugin extra:
                // const ResourceTimelinePlugin = FullCalendar.ResourceTimeline; // npm install @fullcalendar/resource-timeline
                // const ResourceTimeGridPlugin = FullCalendar.ResourceTimeGrid; // npm install @fullcalendar/resource-timegrid

                this.calendar = new Calendar(this.$refs.calendar, {
                    // Si usas vistas de recursos, necesitas añadirlos aquí:
                    // plugins: [ DayGrid, TimeGrid, ListPlugin, InteractionPlugin, ResourceTimelinePlugin ],
                    plugins: [ DayGrid, TimeGrid, ListPlugin, InteractionPlugin ], // Plugins actuales
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                        // Si usas vistas de recursos, podrías añadir algo como:
                        // right: 'resourceTimelineWeek,resourceTimeGridDay,dayGridMonth'
                    },
                    locale: 'es',
                    editable: true,
                    selectable: true,
                    navLinks: true,
                    eventColor: '#378006',

                    // ¡NUEVO! Configura los recursos del calendario
                    resources: this.resources, // Carga los recursos del array $this->resources
                    resourceAreaColumns: [ // Opcional: define las columnas del área de recursos
                        {
                            field: 'title', // Usa 'title' como el campo para mostrar el nombre del veterinario
                            headerContent: 'Veterinario'
                        }
                    ],
                    // Si usas vistas de recursos, aquí es donde lo configuras
                    // resourceGroupField: 'veterinarian_id', // Agrupa eventos por este campo si tienes la vista de recursos
                    // resourceOrder: 'title', // Ordena los recursos por su título

                    events: this.events,

                    dateClick: (info) => {
                        console.log('Date clicked:', info);
                        // Asegúrate de que el ID de Livewire sea el correcto si ha cambiado
                        window.Livewire.find(this.$wire.__instance.id).openCreateModal(info.dateStr, info.date.getDay());
                    },

                    eventClick: (info) => {
                        console.log('Event clicked:', info.event.extendedProps);
                        const eventIdParts = info.event.id.split('-');
                        const eventType = eventIdParts[0];
                        const recordId = eventIdParts[1];
                        // Asegúrate de que el ID de Livewire sea el correcto si ha cambiado
                        window.Livewire.find(this.$wire.__instance.id).openEditModal(eventType, recordId, info.event.extendedProps);
                    },
                });
                this.calendar.render();

                // Observa cambios en las propiedades de Livewire
                Livewire.on('eventsUpdated', () => {
                    this.calendar.removeAllEvents();
                    this.calendar.addEventSource(this.events); // addEventSource debe recibir los eventos directamente
                    this.calendar.refetchEvents(); // Recarga los eventos si ya están en un source
                    // Opcional: si cambian los recursos, también actualizarlos
                    // this.calendar.refetchResources();
                });

                Livewire.on('resourcesUpdated', () => { // ¡NUEVO! Escucha si los recursos se actualizan
                    this.calendar.refetchResources();
                });
            }
        }"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 lg:p-6"
    >
        <div x-ref="calendar"></div>
    </div>

    <x-filament::modal wire:model="createModalOpen" slide-over width="md">
        <x-slot name="heading">
            Crear Evento
        </x-slot>
        <form wire:submit.prevent="save">
            {{ $this->form }}
            <div class="mt-4 flex justify-end gap-x-3">
                <x-filament::button wire:click="closeCreateModal" color="secondary">
                    Cancelar
                </x-filament::button>
                <x-filament::button type="submit">
                    Guardar
                </x-filament::button>
            </div>
        </form>
    </x-filament::modal>

    <x-filament::modal wire:model="editModalOpen" slide-over width="md">
        <x-slot name="heading">
            Editar Evento
        </x-slot>
        <form wire:submit.prevent="update">
            {{ $this->form }}
            <div class="mt-4 flex justify-between">
                <x-filament::button wire:click="delete" color="danger">
                    Eliminar
                </x-filament::button>
                <div class="flex gap-x-3">
                    <x-filament::button wire:click="closeEditModal" color="secondary">
                        Cancelar
                    </x-filament::button>
                    <x-filament::button type="submit">
                        Actualizar
                    </x-filament::button>
                </div>
            </div>
        </form>
    </x-filament::modal>

</x-filament-pages::page>