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
        $appointment = Appointment::with('mascota.cliente.user')->findOrFail($id);

        $mascota = $appointment->mascota;
        $cliente = $mascota->cliente;
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
        $appointment->save();

        return redirect()->route('veterinarian.citas')->with('success', 'Atención guardada correctamente.');
    }

    public function verHistorial($mascotaId, Request $request)
    {
        $mascota = Mascota::with(['cliente.user'])->findOrFail($mascotaId);

        $cliente = $mascota->cliente;
        $usuario = $cliente?->user;

        $from = $request->input('from');
        $to = $request->input('to');

        $registros = $mascota->registrosMedicos()
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

    // ✅ Método para mostrar la vista index.blade.php
    public function index()
    {
        $veterinarian = Auth::user()->veterinarian;
        return view('index', compact('veterinarian'));
    }
}
