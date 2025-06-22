<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CitaCanceladaPorVeterinario extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;
    public $motivo;

    public function __construct(Appointment $appointment, $motivo)
    {
        $this->appointment = $appointment;
        $this->motivo = $motivo;
    }

    public function build()
    {
        return $this->subject('Solicitud de Cancelación de Cita')
                    ->view('emails.cancelacion-cita');
    }
}
