<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Veterinarian;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Mascota;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Models\VeterinarianException;
use App\Mail\CitaCanceladaPorVeterinario;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ReprogrammingRequest;
use App\Models\Specialty; // ¡IMPORTANTE! Asegúrate de que esta línea esté presente
use App\Models\Reminder;
use App\Notifications\ReprogramacionCitaNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use App\Notifications\ReminderNotification;
use Illuminate\Support\Facades\Session;

class VeterinarianController extends Controller
{
    /**
     * Muestra el formulario para editar el perfil de UN veterinario específico por ID.
     * Este método sería usado típicamente por un administrador o para casos especiales.
     * Si el veterinario autenticado edita SU propio perfil, se usa editMyProfile().
     */
    public function edit($id)
    {
        $veterinarian = Veterinarian::with('specialties')->findOrFail($id);

        // Opcional: Si quieres restringir que solo un administrador pueda usar esta ruta
        // y el veterinario autenticado solo pueda usar editMyProfile().
        // if (Auth::user()->role !== 'administrador' && Auth::user()->id !== $veterinarian->user_id) {
        //     abort(403, 'Unauthorized action.');
        // }

        return view('veterinarian.edit', compact('veterinarian'));
    }

    /**
     * Muestra el formulario para editar el perfil del veterinario autenticado.
     * NO requiere un ID en la URL, obtiene el perfil del usuario logueado.
     */
    public function editMyProfile()
    {
        $user = Auth::user();

        $veterinarian = $user->veterinarian()->with('specialties')->first();

        if (!$veterinarian) {
            $veterinarian = new Veterinarian(['user_id' => $user->id]);
            session()->flash('info', 'Bienvenido/a. Por favor, complete la información de su perfil profesional.');
        }

        // AÑADIR esta línea: Cargar todas las especialidades disponibles del sistema
        $specialties = Specialty::all();

        // MODIFICAR esta línea: Pasar $specialties a la vista
        return view('info', compact('veterinarian', 'specialties'));
    }

    /**
     * Actualiza o crea el perfil del veterinario.
     * Este método es el destino de la acción POST/PATCH del formulario de perfil.
     */
    public function update(Request $request)
    {
        $user = Auth::user(); // Obtener el usuario autenticado al principio

        // Iniciar el array de reglas de validación
        $rules = [
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'license_number' => [
                'required', // La licencia es obligatoria
                'string',   // Debe ser una cadena
                'digits_between:4,5',
            ],
            'specialties'    => 'nullable|string',
            'phone'          => [
                'nullable',
                'string',
                'regex:/^\d{3} \d{3} \d{3}$/', // Formato exacto: 3 digitos, espacio, 3 digitos, espacio, 3 digitos
                'size:11'
            ],
            'address'        => 'nullable|string|max:255',
            'bio'            => 'nullable|string|max:2000',
        ];

        // 1. Verificar si AMBOS campos de contraseña están llenos O SI SOLO UNO LO ESTÁ.
        // Queremos forzar la validación de 'required|confirmed' si el usuario TOCA alguno de los campos de contraseña.
        if ($request->filled('password') || $request->filled('password_confirmation')) {
            // Si la contraseña ya fue cambiada antes, NO se permite otro cambio.
            // Esto es lo primero que se evalúa para bloquear futuros cambios.
            if ($user->hasPasswordChanged) {
                return back()->with('error', 'La contraseña ya ha sido cambiada una vez. Contacta al área de administración para futuras modificaciones.');
            } else {
                // Si es la primera vez que se cambia, AÑADIMOS LAS REGLAS DE VALIDACIÓN.
                // Usamos 'sometimes' para que solo se aplique si el campo está presente,
                // pero 'required' dentro asegura que si está presente, no puede estar vacío.
                $rules['password'] = ['sometimes', 'required', 'confirmed', Password::defaults()];
            }
        }

        // Ejecutar TODAS las validaciones ahora.
        // Si 'password' se marcó como 'sometimes' y se intentó rellenar (o solo su confirmación),
        // Laravel aplicará 'required' y 'confirmed'.
        $request->validate($rules);

        // Actualizar datos del usuario (name, email)
        $user->name  = $request->input('name');
        $user->email = $request->input('email');


        if ($request->filled('password') && !$user->hasPasswordChanged) {
            // --- ¡AÑADE ESTAS DOS LÍNEAS PARA DEBUGGING! ---
            Log::info('DEBUG: Intentando cambiar contraseña por primera vez para usuario ID: ' . $user->id);
            Log::info('DEBUG: $user->hasPasswordChanged ANTES de asignar fecha: ' . ($user->hasPasswordChanged ? 'true' : 'false'));
            // --- FIN DEBUGGING ---

            $user->password = Hash::make($request->password);
            $user->password_changed_at = Carbon::now();

            // --- ¡AÑADE ESTA LÍNEA PARA DEBUGGING! ---
            Log::info('DEBUG: password_changed_at asignado en memoria: ' . $user->password_changed_at);
            // --- FIN DEBUGGING ---
        }

        // --- ¡AÑADE ESTA LÍNEA PARA DEBUGGING! ---
        Log::info('DEBUG: Llamando a $user->save() para usuario ID: ' . $user->id);
        // --- FIN DEBUGGING ---
        $user->save();
        // PASO CLAVE 1: Convertir la cadena de especialidades a un array de NOMBRES
        $specialtiesString = $request->input('specialties', '');
        $specialtyNames = array_map('trim', explode(',', $specialtiesString));
        $specialtyNames = array_filter($specialtyNames); // Elimina elementos vacíos

        // PASO CLAVE 2: Obtener los IDs de las especialidades a partir de sus NOMBRES
        $specialtyIds = [];
        if (!empty($specialtyNames)) {
            $specialtyIds = Specialty::whereIn('name', $specialtyNames)->pluck('id')->toArray();
        }

        // Obtener o crear el perfil de veterinario
        $veterinarian = $user->veterinarian; // Asumiendo que ya tienes una instancia cargada (ej. por eager loading o ya existía)

        if ($veterinarian) {
            // Si el perfil ya existe, actualiza sus datos
            $veterinarian->license_number = $request->input('license_number');
            $veterinarian->phone          = $request->input('phone');
            $veterinarian->address        = $request->input('address');
            $veterinarian->bio            = $request->input('bio');
            $veterinarian->save();
            $veterinarian->specialties()->sync($specialtyIds);
        } else {
            // Si el perfil NO existe, crea uno nuevo
            $veterinarian = $user->veterinarian()->create([
                'license_number' => $request->input('license_number'),
                'phone'          => $request->input('phone'),
                'address'        => $request->input('address'),
                'bio'            => $request->input('bio'),
            ]);
            if (!empty($specialtyIds)) {
                $veterinarian->specialties()->attach($specialtyIds);
            }
        }

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    /**
     * Muestra el formulario para atender una cita.
     * Carga las relaciones necesarias para mostrar la información del cliente y mascota.
     */
    public function formularioAtencion($id)
    {
        // CORRECCIÓN: 'mascota.cliente.user' para usar tu modelo 'Cliente'
        $appointment = Appointment::with('mascota.cliente.user')->findOrFail($id);

        // Validación: solo permitir atender citas cuya fecha sea hoy o pasada
        $fechaCita = Carbon::parse($appointment->date)->startOfDay();
        $hoy = now()->startOfDay();

        if ($fechaCita->greaterThan($hoy)) {
            return redirect()->route('veterinarian.citas')
                ->with('error', '⏳ Esta cita aún no puede ser atendida. Solo se podrá atender el día programado o después.');
        }

        $mascota = $appointment->mascota;
        $cliente = $mascota->cliente;
        $usuario = $cliente ? $cliente->user : null;

        return view('atendercita', compact('appointment', 'mascota', 'cliente', 'usuario'));
    }

    /**
     * Guarda la atención de una cita en el historial médico.
     */
    public function guardarAtencion(Request $request)
    {
        $request->validate([
            'appointment_id'        => 'required|exists:appointments,id',
            'diagnosis'             => 'nullable|string',
            'treatment'             => 'nullable|string',
            'notes'                 => 'nullable|string',
            'prescription'          => 'nullable|string',
            'observations'          => 'nullable|string',

            // Validaciones para los campos del recordatorio (si el checkbox está marcado)
            'create_reminder'       => 'boolean',
            'reminder_title'        => 'required_if:create_reminder,1|string|max:255',
            'reminder_description'  => 'nullable|string',
            'remind_at_date'        => 'required_if:create_reminder,1|date|after_or_equal:today',
            'remind_at_time'        => 'required_if:create_reminder,1|date_format:H:i',
        ]);

        // Inicia una transacción de base de datos
        DB::beginTransaction();

        try {
            // 1. Obtener la cita con sus relaciones necesarias
            $appointment = Appointment::with('mascota.cliente.user')->findOrFail($request->appointment_id);
            $mascota = $appointment->mascota;
            $cliente = $mascota->cliente;
            $clientUser = $cliente->user; // El objeto User del cliente

            // dd('DEBUG 1: Datos iniciales', [
            //     'appointment_id' => $request->appointment_id,
            //     'mascota' => $mascota->toArray(),
            //     'cliente' => $cliente->toArray(),
            //     'clientUser' => $clientUser ? $clientUser->toArray() : 'NULL',
            //     'create_reminder_checkbox' => $request->boolean('create_reminder')
            // ]);

            // 2. Crear el registro médico
            $medicalRecord = MedicalRecord::create([
                'mascota_id'                => $mascota->id,
                'veterinarian_id'           => Auth::user()->veterinarian->id,
                'appointment_id'            => $appointment->id,
                'reason_for_consultation'   => $appointment->reason,
                'diagnosis'                 => $request->diagnosis,
                'treatment'                 => $request->treatment,
                'notes'                     => $request->notes,
                'prescription'              => $request->prescription,
                'observations'              => $request->observations,
            ]);

            // dd('DEBUG 2: Registro médico creado', $medicalRecord->toArray());

            // 3. Lógica para crear un recordatorio si el checkbox fue marcado
            if ($request->boolean('create_reminder')) {
                $remindAtDateTime = Carbon::parse($request->remind_at_date . ' ' . $request->remind_at_time);

                // dd('DEBUG 3: Intentando crear recordatorio. Fecha/Hora:', $remindAtDateTime->format('Y-m-d H:i:s'));

                $reminder = Reminder::create([
                    'cliente_id'        => $cliente->id,
                    'mascota_id'        => $mascota->id,
                    'related_to_type'   => 'App\\Models\\MedicalRecord',
                    'related_to_id'     => $medicalRecord->id,
                    'title'             => $request->reminder_title,
                    'description'       => $request->reminder_description,
                    'remind_at'         => $remindAtDateTime,
                    'is_active'         => true,
                ]);

                // dd('DEBUG 4: Recordatorio creado en DB:', $reminder->toArray());

                // 4. Enviar notificación al cliente
                $reminder->save(); // Asegúrate de que el recordatorio esté guardado antes de notificar

                // *** AÑADE ESTE LOG PARA VER SI LLEGAMOS ANTES DE LA CONDICIÓN ***
                Log::info('VeterinarianController: Intentando notificar al cliente.', ['reminder_id' => $reminder->id, 'client_id_from_mascota' => $reminder->mascota->client_id ?? 'N/A']);

                if ($clientUser) {
                    // *** AÑADE ESTE LOG PARA VER SI ENTRAMOS EN LA CONDICIÓN ***
                    Log::info('VeterinarianController: Condición $clientUser es verdadera. Notificando...', ['client_user_id' => $clientUser->id, 'client_email' => $clientUser->email]);

                    $clientUser->notify(new ReminderNotification($reminder));
                    Log::info('VeterinarianController: Llamada a notify() realizada.', ['reminder_id' => $reminder->id, 'client_user_id' => $clientUser->id]);
                    Session::flash('success', 'Recordatorio creado exitosamente y notificación enviada.');
                } else {
                    // *** AÑADE ESTE LOG SI LA CONDICIÓN ES FALSA ***
                    Log::warning('VeterinarianController: No se pudo notificar. $clientUser es nulo o falso.', ['reminder_id' => $reminder->id ?? 'N/A', 'client_user_email' => $clientUser->email ?? 'N/A']);
                    Session::flash('warning', 'Recordatorio creado exitosamente, pero no se pudo enviar la notificación por correo.');
                }
            }

            // 5. Actualizar el estado de la cita a 'completed'
            $appointment->status = 'completed';
            $appointment->end_datetime = Carbon::now();
            $appointment->save();

            // dd('DEBUG 6: Cita actualizada a completada');

            // 6. Confirmar la transacción
            DB::commit();

            return redirect()->route('veterinarian.citas')->with('success', 'Atención guardada correctamente y recordatorio (si se programó) enviado.');
        } catch (\Exception $e) {
            // 7. Revertir la transacción si hay un error
            DB::rollBack();
            Log::error('Error al guardar atención médica o recordatorio: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            // dd('DEBUG 7: Error atrapado', $e->getMessage(), $e->getTraceAsString());
            return redirect()->back()->with('error', 'Ocurrió un error al procesar la atención médica. Por favor, inténtalo de nuevo.');
        }
    }
    /**
     * Muestra el historial médico de una mascota específica.
     * Carga la mascota con las relaciones cliente y usuario.
     */
    public function verHistorial($mascotaId, Request $request)
    {
        // CORRECCIÓN: 'cliente.user' para usar tu modelo 'Cliente'
        $mascota = Mascota::with(['cliente.user'])->findOrFail($mascotaId);

        $cliente = $mascota->cliente;
        $usuario = $cliente?->user;

        $from = $request->input('from');
        $to = $request->input('to');

        $registros = $mascota->registrosMedicos()
            ->with(['veterinarian.user', 'service'])
            ->when($from, fn($query) => $query->where('consultation_date', '>=', $from))
            ->when($to, fn($query) => $query->where('consultation_date', '<=', $to))
            ->orderBy('consultation_date', 'desc')
            ->get();

        return view('historialmascota', [
            'mascota' => $mascota,
            'cliente' => $cliente,
            'usuario' => $usuario,
            'registros' => $registros
        ]);
    }

    /**
     * Muestra la vista de información del perfil del veterinario (`info.blade.php`).
     * Este método redirige al veterinario a completar su perfil si no existe.
     */
    public function showProfile()
    {
        $user = Auth::user();

        // Cargar el perfil del veterinario EAGER LOADING con las relaciones 'user' y 'specialties'.
        $veterinarian = $user->veterinarian()->with(['user', 'specialties'])->first();

        if (!$veterinarian) {
            Log::warning('Intento de acceso a perfil de veterinario sin perfil existente.', ['user_id' => $user->id]);
            // Redirige al veterinario a la página para que complete su perfil
            return redirect()->route('veterinarian.edit.my')->with('info', 'Su perfil de veterinario no está completo. Por favor, edítelo.');
        }

        $unreadCount = $user->unreadNotifications()->count();

        return view('info', compact('veterinarian', 'unreadCount'));
    }

    /**
     * Muestra la vista principal del dashboard del veterinario (`index.blade.php`).
     * Maneja el caso en que el perfil del veterinario aún no ha sido creado.
     */
    public function index()
    {
        $user = Auth::user();

        $veterinarian = $user->veterinarian()->with('specialties')->first();

        if (strtolower($user->role) !== 'veterinario') {
            return redirect()->route('veterinarian.login'); // O a la vista de inicio adecuada
        }

        // Cargar el perfil del veterinario con la relación 'specialties'.
        $veterinarian = $user->veterinarian()->with('specialties')->first();

        // Si el perfil de veterinario no existe, crea una instancia vacía para
        // que la vista `index` (si muestra parte del perfil) no falle.
        // Y se envía un mensaje para que complete su perfil.
        if (!$veterinarian) {
            $veterinarian = new Veterinarian(['user_id' => $user->id]);
            session()->flash('info', 'Por favor complete su perfil para configurar su información profesional.');
        }

        return view('index', compact('user', 'veterinarian'));
    }

    /**
     * Permite al veterinario cancelar una cita.
     * Envía un correo de notificación al cliente.
     */
    public function cancelarCita(Request $request)
    {
        Log::info('✅ Entró al método cancelarCita con datos:', $request->all());

        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'motivo' => 'required|string|max:500',
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);

        VeterinarianException::create([
            'veterinarian_id' => auth()->user()->veterinarian->id,
            'date' => $appointment->date,
            'notes' => $request->motivo,
        ]);

        try {
            // CORRECCIÓN: 'mascota.cliente.user.email' para usar tu modelo 'Cliente'
            $clientEmail = $appointment->mascota->cliente->user->email;
            Mail::to($clientEmail)->send(new \App\Mail\CitaCanceladaPorVeterinario($appointment, $request->motivo));
            Log::info('Correo de cancelación enviado exitosamente al cliente: ' . $clientEmail);
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de cancelación: ' . $e->getMessage());
            return redirect()->route('veterinarian.citas')->with('error', 'No se pudo enviar el correo de cancelación. Error: ' . $e->getMessage());
        }

        $appointment->status = 'cancelled';
        $appointment->save();

        return redirect()->route('veterinarian.citas')->with('success', 'Cita cancelada y notificación enviada al cliente.');
    }

    /**
     * Muestra datos estadísticos relevantes para el veterinario.
     */
    public function datosEstadisticos()
    {
        $veterinario = auth()->user()->veterinarian;

        if (!$veterinario) {
            return redirect()->route('veterinarian.edit.my')
                ->with('info', 'Por favor, complete su perfil para ver las estadísticas.');
        }

        $labels = collect();
        $data = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dia = now()->subDays($i);
            $labels->push($dia->format('d M'));
            $data->push(
                \App\Models\Appointment::where('veterinarian_id', $veterinario->id)
                    ->where('status', 'completed')
                    ->whereDate('date', $dia->toDateString())
                    ->count()
            );
        }

        $servicios = \App\Models\Appointment::join('services', 'appointments.service_id', '=', 'services.id')
            ->where('appointments.veterinarian_id', $veterinario->id)
            ->where('appointments.status', 'completed')
            ->groupBy('services.name')
            ->select('services.name as nombre', DB::raw('COUNT(*) as total'))
            ->orderByDesc('total') // opcional: ordena de mayor a menor
            ->get();



        $totalCompletadas = \App\Models\Appointment::where('veterinarian_id', $veterinario->id)
            ->where('status', 'completed')
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $totalPendientes = \App\Models\Appointment::where('veterinarian_id', $veterinario->id)
            ->where('status', 'pending')
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $totalCanceladas = \App\Models\Appointment::where('veterinarian_id', $veterinario->id)
            ->where('status', 'cancelled')
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return view('datosestadisticos', compact(
            'labels',
            'data',
            'servicios',
            'totalCompletadas',
            'totalPendientes',
            'totalCanceladas'
        ));
    }

    /**
     * Muestra las notificaciones del veterinario, incluyendo solicitudes de reprogramación
     * y citas próximas.
     */
    public function notificaciones()
    {
        $veterinarianId = Auth::user()->veterinarian->id;

        // CORRECCIÓN: 'appointment.mascota.cliente.user' y 'client.user'
        $reprogrammingRequests = ReprogrammingRequest::where('veterinarian_id', $veterinarianId)
            ->whereIn('status', ['pending_client_confirmation', 'pending_veterinarian_confirmation'])

            ->whereHas('appointment', function ($query) {
                $query->where('status', '!=', 'cancelled')
                    ->whereNull('deleted_at');
            })
            ->with([
                'appointment.mascota.cliente.user', // Asegura cargar la cadena completa
                'client.user', // Si el 'client' en ReprogrammingRequest es un Cliente
                'veterinarian.user'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $tomorrow = Carbon::tomorrow(new \DateTimeZone('America/Lima'));
        $endOfDayTomorrow = Carbon::tomorrow(new \DateTimeZone('America/Lima'))->endOfDay();

        // CORRECCIÓN: 'mascota.cliente.user'
        $citas = Appointment::where('veterinarian_id', $veterinarianId)
            ->whereBetween('date', [$tomorrow, $endOfDayTomorrow])
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['mascota.cliente.user']) // Asegura cargar la cadena completa
            ->orderBy('date', 'asc')
            ->get();

        $unreadCount = $reprogrammingRequests->count();

        session(['notificaciones_vistas.' . $tomorrow->toDateString() => true]);

        return view('notificaciones', compact('reprogrammingRequests', 'citas', 'unreadCount'));
    }

    /**
     * Permite al veterinario proponer una reprogramación de cita al cliente.
     */

    public function reprogramarCita(Request $request)
    {
        $request->validate([
            'appointment_id'     => 'required|exists:appointments,id',
            'nueva_fecha'        => 'required|date|after_or_equal:now',
            'reprogramming_reason' => 'nullable|string|max:1000',
        ]);

        // CORRECCIÓN: 'mascota.cliente' para usar tu modelo 'Cliente'
        $appointment = Appointment::with('mascota.cliente')->findOrFail($request->appointment_id);

        if (Auth::user()->veterinarian->id !== $appointment->veterinarian_id) {
            return redirect()->back()->with('error', 'No tienes permiso para reprogramar esta cita.');
        }

        $originalStart = Carbon::parse($appointment->date);
        $originalEnd = $appointment->end_datetime ? Carbon::parse($appointment->end_datetime) : null;

        $durationInMinutes = 60; // Duración por defecto si no se puede calcular
        if ($originalEnd) {
            $durationInMinutes = $originalStart->diffInMinutes($originalEnd);
        } else {
            if ($appointment->service && $appointment->service->duration_minutes) {
                $durationInMinutes = $appointment->service->duration_minutes;
            }
        }

        $proposedStart = Carbon::parse($request->nueva_fecha);
        $proposedEnd = $proposedStart->copy()->addMinutes($durationInMinutes);

        $client_id = null;
        // CORRECCIÓN: $appointment->mascota->cliente
        if ($appointment->mascota && $appointment->mascota->cliente) {
            $client_id = $appointment->mascota->cliente->id;
        } else {
            Log::error("Error al reprogramar cita #{$appointment->id}: No se pudo encontrar el cliente asociado a la mascota.");
            return redirect()->back()->with('error', 'No se pudo procesar la solicitud de reprogramación. Faltan datos del cliente.');
        }

        $veterinarian_id = null;
        if (auth()->user()->veterinarian) {
            $veterinarian_id = auth()->user()->veterinarian->id;
        } else {
            Log::error("Error al reprogramar cita: El usuario autenticado no tiene un registro de veterinario.");
            return redirect()->back()->with('error', 'Tu cuenta no está asociada a un perfil de veterinario válido.');
        }

        try {
            $reprogrammingRequest = ReprogrammingRequest::create([
                'appointment_id'           => $appointment->id,
                'client_id'                => $client_id,
                'veterinarian_id'          => $veterinarian_id,
                'requester_type'           => 'veterinarian',
                'requester_user_id'        => auth()->id(),
                'proposed_start_date_time' => $proposedStart,
                'proposed_end_date_time'   => $proposedEnd,
                'reprogramming_reason'     => $request->reprogramming_reason,
                'client_confirmed'         => false,
                'veterinarian_confirmed'   => true, // El veterinario inicia la propuesta, por eso él ya la "confirma"
                'status'                   => 'pending_client_confirmation', // Pendiente de la respuesta del cliente
            ]);


            // 🔔 Enviar notificación al cliente
            $cliente = $appointment->mascota->cliente->user ?? null;


            if ($cliente) {
                // dd($reprogrammingRequest);
                $cliente->notify(new ReprogramacionCitaNotification(
                    $appointment,
                    $proposedStart,
                    $request->reprogramming_reason,
                    $reprogrammingRequest // 👈 Aquí le pasas el objeto creado
                ));
            }



            return redirect()->route('veterinarian.citas')->with('success', 'Propuesta de reprogramación enviada al cliente.');
        } catch (\Exception $e) {
            Log::error('Error al crear la solicitud de reprogramación: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Ocurrió un error al reprogramar la cita. Intenta nuevamente.');
        }
    }

    /**
     * Permite al veterinario aceptar una solicitud de reprogramación iniciada por el cliente.
     */
    public function aceptarReprogramacion(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:reprogramming_requests,id',
        ]);

        $reprogrammingRequest = ReprogrammingRequest::findOrFail($request->request_id);

        if (Auth::user()->veterinarian->id !== $reprogrammingRequest->veterinarian_id) {
            return redirect()->back()->with('error', 'No tienes permiso para interactuar con esta solicitud.');
        }

        // Verifica que la solicitud sea del cliente y esté pendiente de confirmación del veterinario
        if ($reprogrammingRequest->requester_type === 'client' && $reprogrammingRequest->status === 'pending_veterinarian_confirmation') {
            $appointment = Appointment::findOrFail($reprogrammingRequest->appointment_id);

            // Actualiza la cita con la nueva fecha y hora propuesta por el cliente
            $appointment->date = $reprogrammingRequest->proposed_start_date_time;
            $appointment->end_datetime = $reprogrammingRequest->proposed_end_date_time;
            $appointment->status = 'reprogrammed'; // Nuevo estado para cita reprogramada
            $appointment->save();

            // Actualiza el estado de la solicitud de reprogramación
            $reprogrammingRequest->veterinarian_confirmed = true;
            $reprogrammingRequest->status = 'accepted_by_veterinarian'; // Estado final de la solicitud
            $reprogrammingRequest->save();

            return redirect()->route('veterinarian.notificaciones')->with('success', 'Solicitud de reprogramación aceptada y cita actualizada.');
        } else {
            return redirect()->back()->with('error', 'No puedes aceptar esta solicitud en su estado actual o no está pendiente de tu confirmación.');
        }
    }

    public function confirmarCita(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
        ]);

        $cita = Appointment::findOrFail($request->appointment_id);
        $cita->status = 'confirmed';
        $cita->save();

        return back()->with('success', 'La cita ha sido confirmada.');
    }

    /**
     * Permite al veterinario retirar una propuesta de reprogramación que él mismo inició.
     */
    public function retirarPropuestaReprogramacion(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:reprogramming_requests,id',
        ]);

        $reprogrammingRequest = ReprogrammingRequest::findOrFail($request->request_id);

        // Asegura que el veterinario autenticado sea el que hizo la propuesta y que esté pendiente
        if (Auth::user()->veterinarian->id !== $reprogrammingRequest->veterinarian_id || $reprogrammingRequest->requester_type !== 'veterinarian') {
            return redirect()->back()->with('error', 'No tienes permiso para retirar esta propuesta.');
        }

        if ($reprogrammingRequest->status !== 'pending_client_confirmation') {
            return redirect()->back()->with('error', 'Esta propuesta ya no está en estado de "pendiente de confirmación del cliente" y no puede ser retirada.');
        }

        // Cambia el estado de la solicitud a retirada por el veterinario
        $reprogrammingRequest->status = 'cancelled_by_request';
        $reprogrammingRequest->save();

        // Puedes considerar revertir el estado de la cita a 'pending' o 'confirmed'
        // si la propuesta fue retirada antes de ser aceptada.
        // Por ejemplo:
        // $appointment = Appointment::findOrFail($reprogrammingRequest->appointment_id);
        // if ($appointment->status === 'reprogramming_proposed') {
        //     $appointment->status = 'pending'; // O el estado anterior adecuado
        //     $appointment->save();
        // }

        return redirect()->route('veterinarian.notificaciones')->with('success', 'Propuesta de reprogramación retirada exitosamente.');
    }

    // --- Métodos adicionales (si existen en tu controlador original y los necesitas) ---

    // Este método solo si lo estás utilizando para obtener horarios disponibles
    public function getAvailableSchedules($appointmentId)
    {
        // Tu lógica para obtener horarios disponibles aquí
        return response()->json(['message' => 'Lógica de horarios disponibles']);
    }

    // Este método solo si lo estás utilizando para rechazar reprogramaciones
    public function rechazarReprogramacion(Request $request)
    {
        // Tu lógica para rechazar reprogramación aquí
        return redirect()->back()->with('success', 'Reprogramación rechazada');
    }

    public function searchSpecialties(Request $request)
    {
        $query = $request->input('query');

        // Valida que la consulta no esté vacía y tenga al menos 2 caracteres
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]); // Devuelve un array vacío si no hay suficiente consulta
        }

        // Busca especialidades que coincidan con la consulta (insensible a mayúsculas/minúsculas)
        // 'LIKE' para MySQL/SQLite, 'ILIKE' para PostgreSQL
        $specialties = Specialty::where('name', 'LIKE', '%' . $query . '%')
            ->pluck('name') // Obtiene solo los nombres de las especialidades
            ->toArray();    // Convierte la colección a un array simple de PHP

        return response()->json($specialties);
    }
}
