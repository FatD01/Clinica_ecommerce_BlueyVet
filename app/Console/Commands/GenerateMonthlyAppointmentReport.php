<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Appointment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage; // Para guardar el PDF temporalmente
use App\Mail\MonthlyAppointmentReport; // Tu Mailable

class GenerateMonthlyAppointmentReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:monthly-appointments {--email= : Email to send the report to (default: admin@example.com)} {--month= : Month number (1-12) to generate report for (default: last month)} {--year= : Year to generate report for (default: current year)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates and sends a monthly appointment report via email.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Determinar el mes y año para el reporte
        $requestedMonth = $this->option('month');
        $requestedYear = $this->option('year');

        // Por defecto, usa el mes anterior y el año actual
        if (empty($requestedMonth)) {
            $date = Carbon::now()->subMonth();
            $month = $date->month;
            $year = $date->year;
        } else {
            $month = (int) $requestedMonth;
            $year = empty($requestedYear) ? Carbon::now()->year : (int) $requestedYear;
            $date = Carbon::create($year, $month, 1);
        }

        $monthName = $date->locale('es')->monthName; // Nombre del mes en español

        $this->info("Generando reporte de citas para {$monthName} de {$year}...");

        // 1. Obtener los datos para el reporte
        $appointments = Appointment::with(['mascota', 'veterinarian.user'])
            ->whereMonth('date', $month) // Filtra por el mes de la cita
            ->whereYear('date', $year)   // Filtra por el año de la cita
            // Puedes añadir más filtros si quieres que el reporte automático tenga criterios específicos
            // ->where('status', 'completed') // Por ejemplo, solo citas completadas
            ->get();

        $this->info("Se encontraron " . $appointments->count() . " citas.");

        // 2. Generar el PDF
        $pdf = Pdf::loadView('PdfOrderProducts', [
            'appointments' => $appointments,
            'export_date' => Carbon::now()->format('d/m/Y H:i:s'),
            'active_filters' => [ // Puedes indicar los filtros que se aplicaron automáticamente
                ['label' => 'Mes del Reporte', 'value' => "{$monthName} de {$year}"],
            ],
            'total_appointments' => $appointments->count(),
        ]);

        // 3. Guardar el PDF temporalmente
        $filename = "reporte_citas_mensual_{$monthName}_{$year}.pdf";
        $path = 'public/reports/' . $filename; // Guardar en storage/app/public/reports
        Storage::put($path, $pdf->output());

        // Obtener la ruta absoluta para el adjunto
        $absolutePath = Storage::path($path);

        $this->info("PDF generado y guardado en: " . $absolutePath);

        // 4. Enviar el correo electrónico
        $recipientEmail = $this->option('email') ?? config('mail.from.address'); // Usa el email del config si no se especifica

        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error("La dirección de correo electrónico '{$recipientEmail}' no es válida.");
            return Command::FAILURE;
        }

        try {
            Mail::to($recipientEmail)->send(new MonthlyAppointmentReport($monthName, $year, $absolutePath, $filename));
            $this->info("Reporte enviado exitosamente a {$recipientEmail}.");
            // 5. Borrar el PDF temporal después de enviarlo (opcional, pero buena práctica)
            Storage::delete($path);
            $this->info("Archivo temporal del PDF borrado.");

        } catch (\Exception $e) {
            $this->error("Error al enviar el correo: " . $e->getMessage());
            // No borrar el archivo si hubo un error en el envío, para depuración
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}