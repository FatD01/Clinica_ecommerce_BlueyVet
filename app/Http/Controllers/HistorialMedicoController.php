<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mascota;
use App\Models\Appointment;
use Carbon\Carbon;

class HistorialMedicoController extends Controller
{
    public function index(Request $request)
    {
        $veterinario = Auth::user()->veterinarian;

        $mascotaSeleccionadaId = $request->input('mascota_id');

        // Mascotas del veterinario
        $query = Mascota::whereHas('appointments', function ($q) use ($veterinario) {
            $q->where('veterinarian_id', $veterinario->id);
        });

        // Filtra si seleccionó una mascota
        if ($mascotaSeleccionadaId) {
            $query->where('id', $mascotaSeleccionadaId);
        }

        $mascotas = $query->distinct()->get();

        // Todas las mascotas del veterinario para el dropdown
        $todasMascotas = Mascota::whereHas('appointments', function ($q) use ($veterinario) {
            $q->where('veterinarian_id', $veterinario->id);
        })->distinct()->get();

        return view('historialmedico', compact('mascotas', 'todasMascotas'));
    }

    public function verHistorial(Request $request, $id)
    {
        $mascota = Mascota::findOrFail($id);
        $veterinario = Auth::user()->veterinarian;

        $query = Appointment::where('mascota_id', $mascota->id)
            ->where('veterinarian_id', $veterinario->id)
            ->whereHas('medicalRecord', function ($q) {
                $q->whereNotNull('diagnosis');
            });

        if ($request->filled('from')) {
            $from = Carbon::parse($request->input('from'))->startOfDay();
            $query->where('date', '>=', $from);
        }

        if ($request->filled('to')) {
            $to = Carbon::parse($request->input('to'))->endOfDay();
            $query->where('date', '<=', $to);
        }

        $historiales = $query->orderBy('date', 'desc')->paginate(5);

        return view('historialmascota', [
            'mascota' => $mascota,
            'historiales' => $historiales,
            'from' => $request->input('from'),
            'to' => $request->input('to')
        ]);
    }
}
