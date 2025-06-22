<?php

namespace App\Exports;

use App\Models\Mascota; // Usamos tu modelo Mascota
use App\Models\Cliente; // Usamos tu modelo Cliente
use App\Models\User;    // Usamos tu modelo User
use App\Models\MedicalRecord; // Usamos tu modelo MedicalRecord

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable; // Para permitir el uso de ->download()
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Para que las columnas se ajusten automáticamente si exportas a Excel

class MedicalHistoryFullExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $mascotaId;
    protected $from;
    protected $to;

    public function __construct(int $mascotaId, ?string $from = null, ?string $to = null)
    {
        $this->mascotaId = $mascotaId;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Retorna la vista Blade que servirá como plantilla para el PDF.
     * @return \Illuminate\Contracts\View\View
     */
    public function view(): View
    {
        // Carga la mascota con sus relaciones necesarias (registros médicos, cliente, y usuario del cliente)
        $mascota = Mascota::with(['registrosMedicos', 'cliente.user'])->findOrFail($this->mascotaId);

        // Prepara la consulta para los registros médicos, aplicando los filtros de fecha
        $queryRegistros = $mascota->registrosMedicos()->orderBy('consultation_date', 'asc');

        if ($this->from) {
            $queryRegistros->whereDate('consultation_date', '>=', $this->from);
        }

        if ($this->to) {
            $queryRegistros->whereDate('consultation_date', '<=', $this->to);
        }

        $registros = $queryRegistros->get();

        // Pasa los datos a la vista Blade
        return view('veterinarian.exports.medical_history_full_pdf', [
            'mascota' => $mascota,
            'registros' => $registros,
            'cliente' => $mascota->cliente,
            'usuario' => $mascota->cliente->user ?? null, // Asegúrate de manejar si el user es null
        ]);
    }
}