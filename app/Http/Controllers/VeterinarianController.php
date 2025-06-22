<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Veterinarian;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Mascota;
use App\Models\Client; // Se mantiene el import, aunque uses Cliente::class en la relación.
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Models\VeterinarianException;
use App\Mail\CitaCanceladaPorVeterinario;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ReprogrammingRequest;

class VeterinarianController extends Controller
{
    public function edit($id)
    {
        $veterinarian = Veterinarian::findOrFail($id);
        return view('veterinarian.edit', compact('veterinarian'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'license_number' => 'required|string|max:100',
            'specialty'      => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'bio'            => 'nullable|string|max:2000',
        ]);

        $user = Auth::user();

        $user->name  = $request->input('name');
        $user->email = $request->input('email');
        $user->save();

        $veterinarian = $user->veterinarian;

        if ($veterinarian) {
            $veterinarian->license_number = $request->input('license_number');
            $veterinarian->specialty      = $request->input('specialty');
            $veterinarian->phone          = $request->input('phone');
            $veterinarian->address        = $request->input('address');
            $veterinarian->bio            = $request->input('bio');
            $veterinarian->save();
        } else {
            $user->veterinarian()->create([
                'license_number' => $request->input('license_number'),
                'specialty'      => $request->input('specialty'),
                'phone'          => $request->input('phone'),
                'address'        => $request->input('address'),
                'bio'            => $request->input('bio'),
            ]);
        }

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function formularioAtencion($id)
    {
        // CORRECCIÓN AQUÍ: 'mascota.cliente.user' en lugar de 'mascota.client.user'
        $appointment = Appointment::with('mascota.cliente.user')->findOrFail($id);

        // Validación: solo permitir atender citas cuya fecha sea hoy o pasada
        $fechaCita = \Carbon\Carbon::parse($appointment->date)->format('Y-m-d');
        $hoy = now()->format('Y-m-d');

        if ($fechaCita > $hoy) {
            return redirect()->route('veterinarian.citas')
                ->with('error', '⏳ Esta cita aún no puede ser atendida. Solo se podrá atender el día programado o después.');
        }

        $mascota = $appointment->mascota;
        $cliente = $mascota->cliente; // CORRECCIÓN AQUÍ: $mascota->cliente
        $usuario = $cliente ? $cliente->user : null;

        return view('atendercita', compact('appointment', 'mascota', 'cliente', 'usuario'));
    }


    public function guardarAtencion(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'diagnosis'      => 'nullable|string',
            'treatment'      => 'nullable|string',
            'notes'          => 'nullable|string',
            'prescription'   => 'nullable|string',
            'observations'   => 'nullable|string',
        ]);

        $appointment = Appointment::with('mascota')->findOrFail($request->appointment_id);
        $mascota = $appointment->mascota;

        MedicalRecord::create([
            'mascota_id'             => $mascota->id,
            'veterinarian_id'        => Auth::user()->veterinarian->id,
            'appointment_id'         => $appointment->id,
            'reason_for_consultation'=> $appointment->reason,
            'diagnosis'              => $request->diagnosis,
            'treatment'              => $request->treatment,
            'notes'                  => $request->notes,
            'prescription'           => $request->prescription,
            'observations'           => $request->observations,
        ]);

        $appointment->status = 'completed';
        $appointment->end_datetime = Carbon::now();
        $appointment->save();

        return redirect()->route('veterinarian.citas')->with('success', 'Atención guardada correctamente.');
    }

    public function verHistorial($mascotaId, Request $request)
    {
        // CORRECCIÓN AQUÍ: 'cliente.user' en lugar de 'client.user'
        $mascota = Mascota::with(['cliente.user'])->findOrFail($mascotaId);

        $cliente = $mascota->cliente; // CORRECCIÓN AQUÍ: $mascota->cliente
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

    public function index()
    {
        $veterinarian = Auth::user()->veterinarian;
        return view('index', compact('veterinarian'));
    }

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
            // CORRECCIÓN AQUÍ: $appointment->mascota->cliente->user->email
            $clientEmail = $appointment->mascota->cliente->user->email;
            Mail::to($clientEmail)->send(new \App\Mail\CitaCanceladaPorVeterinario($appointment, $request->motivo));
            Log::info('Correo de cancelación enviado exitosamente al cliente: ' . $clientEmail);
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de cancelación: ' . $e->getMessage());
            return redirect()->route('veterinarian.citas')->with('error', 'No se pudo enviar el correo de cancelación. Error: ' . $e->getMessage());
        }

        $appointment->status = 'canceled';
        $appointment->save();

        return redirect()->route('veterinarian.citas')->with('success', 'Cita cancelada y notificación enviada al cliente.');
    }

    public function datosEstadisticos()
    {
        $veterinario = auth()->user()->veterinarian;

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

        $servicios = \App\Models\Appointment::select('service_id', \DB::raw('COUNT(*) as total'))
            ->where('veterinarian_id', $veterinario->id)
            ->where('status', 'completed')
            ->groupBy('service_id')
            ->with('service')
            ->get()
            ->map(fn($s) => [
                'nombre' => $s->service->name ?? 'Desconocido',
                'total' => $s->total
            ]);

        $totalCompletadas = \App\Models\Appointment::where('veterinarian_id', $veterinario->id)
            ->where('status', 'completed')
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $totalPendientes = \App\Models\Appointment::where('veterinarian_id', $veterinario->id)
            ->where('status', 'pending')
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $totalCanceladas = \App\Models\Appointment::where('veterinarian_id', $veterinario->id)
            ->where('status', 'canceled')
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return view('datosestadisticos', compact(
            'labels', 'data', 'servicios',
            'totalCompletadas', 'totalPendientes', 'totalCanceladas'
        ));
    }

    public function notificaciones()
    {
        $veterinarianId = Auth::user()->veterinarian->id;

        // CORRECCIÓN AQUÍ: 'appointment.mascota.cliente.user' en lugar de 'appointment.mascota.client.user'
        $reprogrammingRequests = ReprogrammingRequest::where('veterinarian_id', $veterinarianId)
            ->whereIn('status', ['pending_client_confirmation', 'pending_veterinarian_confirmation'])
            ->with([
                'appointment.mascota.cliente.user', // CORRECCIÓN AQUÍ
                'client.user',
                'veterinarian.user'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $tomorrow = Carbon::tomorrow(new \DateTimeZone('America/Lima'));
        $endOfDayTomorrow = Carbon::tomorrow(new \DateTimeZone('America/Lima'))->endOfDay();

        // CORRECCIÓN AQUÍ: 'mascota.cliente.user' en lugar de 'mascota.client.user'
        $citas = Appointment::where('veterinarian_id', $veterinarianId)
            ->whereBetween('date', [$tomorrow, $endOfDayTomorrow])
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['mascota.cliente.user']) // CORRECCIÓN AQUÍ
            ->orderBy('date', 'asc')
            ->get();

        $unreadCount = $reprogrammingRequests->count();

        session(['notificaciones_vistas.' . $tomorrow->toDateString() => true]);

        return view('notificaciones', compact('reprogrammingRequests', 'citas', 'unreadCount'));
    }

    public function reprogramarCita(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'nueva_fecha' => 'required|date|after_or_equal:now',
            'reprogramming_reason' => 'nullable|string|max:1000',
        ]);

        // CORRECCIÓN AQUÍ: 'mascota.cliente' en lugar de 'mascota.client'
        $appointment = Appointment::with('mascota.cliente')->findOrFail($request->appointment_id);

        if (Auth::user()->veterinarian->id !== $appointment->veterinarian_id) {
            return redirect()->back()->with('error', 'No tienes permiso para reprogramar esta cita.');
        }

        $originalStart = Carbon::parse($appointment->date);
        $originalEnd = $appointment->end_datetime ? Carbon::parse($appointment->end_datetime) : null;

        $durationInMinutes = 60;
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
        // CORRECCIÓN AQUÍ: $appointment->mascota->cliente
        if ($appointment->mascota && $appointment->mascota->cliente) {
            $client_id = $appointment->mascota->cliente->id; // CORRECCIÓN AQUÍ
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
                'veterinarian_confirmed'   => true,
                'status'                   => 'pending_client_confirmation',
            ]);

            $appointment->status = 'reprogramming_proposed';
            $appointment->save();

            return redirect()->route('veterinarian.citas')->with('success', 'Propuesta de reprogramación enviada al cliente.');

        } catch (\Exception $e) {
            Log::error('Error al crear la solicitud de reprogramación: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Hubo un error al procesar la solicitud de reprogramación. Inténtalo de nuevo.');
        }
    }

    public function aceptarReprogramacion(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:reprogramming_requests,id',
        ]);

        $reprogrammingRequest = ReprogrammingRequest::findOrFail($request->request_id);

        if (Auth::user()->veterinarian->id !== $reprogrammingRequest->veterinarian_id) {
            return redirect()->back()->with('error', 'No tienes permiso para interactuar con esta solicitud.');
        }

        if ($reprogrammingRequest->requester_type === 'veterinarian' && $reprogrammingRequest->status !== 'pending_client_confirmation') {
             return redirect()->back()->with('error', 'Esta solicitud no está pendiente de tu confirmación o ya fue gestionada.');
        }

        if ($reprogrammingRequest->requester_type === 'client' && $reprogrammingRequest->status === 'pending_veterinarian_confirmation') {
            $appointment = Appointment::findOrFail($reprogrammingRequest->appointment_id);
            $appointment->date = $reprogrammingRequest->proposed_start_date_time;
            $appointment->end_datetime = $reprogrammingRequest->proposed_end_date_time;
            $appointment->status = 'reprogrammed';
            $appointment->save();

            $reprogrammingRequest->veterinarian_confirmed = true;
            $reprogrammingRequest->status = 'accepted_by_veterinarian';
            $reprogrammingRequest->save();

            return redirect()->route('veterinarian.notificaciones')->with('success', 'Solicitud de reprogramación aceptada y cita actualizada.');

        } else {
            return redirect()->back()->with('error', 'No puedes aceptar esta solicitud en su estado actual.');
        }
    }

    public function retirarPropuestaReprogramacion(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:reprogramming_requests,id',
        ]);

        $reprogrammingRequest = ReprogrammingRequest::findOrFail($request->request_id);

        if (Auth::user()->veterinarian->id !== $reprogrammingRequest->veterinarian_id || $reprogrammingRequest->requester_type !== 'veterinarian') {
            return redirect()->back()->with('error', 'No tienes permiso para retirar esta propuesta.');
        }

        if ($reprogrammingRequest->status !== 'pending_client_confirmation') {
            return redirect()->back()->with('error', 'Esta propuesta ya no está en estado de "pendiente de confirmación del cliente" y no puede ser retirada.');
        }

        $reprogrammingRequest->status = 'withdrawn_by_veterinarian';
        $reprogrammingRequest->save();

        return redirect()->route('veterinarian.notificaciones')->with('success', 'Propuesta de reprogramación retirada exitosamente.');
    }
}