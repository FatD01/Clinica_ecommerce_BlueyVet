<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mascota;
use App\Models\Appointment;
use App\Models\Cliente; // Tu modelo de Cliente
use App\Models\MedicalRecord; 
use Carbon\Carbon;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel; // ¡Importa el Facade de Laravel Excel!
use App\Exports\MedicalHistoryFullExport; // ¡Importa tu clase Exporter!
use Barryvdh\DomPDF\Facade\Pdf; // Importa el Facade de DomPDF
use Illuminate\Support\Str; // Importa el Facade de Str

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

    public function exportHistorialCompleto(Mascota $mascota)
    {
        // Carga todos los MedicalRecords asociados a la mascota
        // Asegúrate de eager load las relaciones que necesitas en la vista (veterinarian, service)
        $historiales = MedicalRecord::where('mascota_id', $mascota->id)
                                    ->with('veterinarian.user', 'service')
                                    ->orderBy('consultation_date', 'desc')
                                    ->get();

        // Puedes cargar también los datos del cliente si los necesitas en el PDF
        $cliente = $mascota->cliente->user; // Accede al usuario del cliente

        // Carga la vista que servirá de plantilla para el PDF
        $pdf = Pdf::loadView('pdf.historial_completo', compact('mascota', 'historiales', 'cliente'));

        // Opcional: Establecer el tamaño del papel y la orientación (por defecto es A4 portrait)
        // $pdf->setPaper('a4', 'landscape');

        // Descarga el PDF con un nombre de archivo
        return $pdf->download('historial_medico_' . Str::slug($mascota->name) . '.pdf');
    }

    /**
     * Exporta una única cita (MedicalRecord) a PDF.
     *
     * @param  \App\Models\MedicalRecord  $registro
     * @return \Illuminate\Http\Response
     */
    public function exportCita(MedicalRecord $registro)
    {
        // Asegúrate de eager load las relaciones que necesitas en la vista (mascota, veterinarian, service)
        $registro->load('mascota.cliente.user', 'veterinarian.user', 'service');

        // Carga la vista que servirá de plantilla para el PDF
        $pdf = Pdf::loadView('pdf.cita_medica', compact('registro'));

        // Descarga el PDF con un nombre de archivo
        return $pdf->download('cita_medica_' . $registro->id . '_' . Str::slug($registro->mascota->name) . '.pdf');
    }
    
}
