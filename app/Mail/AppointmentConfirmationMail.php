<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Appointment; // Asegúrate de importar el modelo Appointment
use Illuminate\Mail\Mailables\Attachment; // Importar Attachment
use Illuminate\Support\Facades\Log; // Importar Log facade

class AppointmentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;
    public $appointmentPdfPath;
    public $paymentPdfPath;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Appointment $appointment
     * @param string $appointmentPdfPath Ruta completa al PDF de la cita
     * @param string $paymentPdfPath Ruta completa al PDF del pago
     * @return void
     */
    public function __construct(Appointment $appointment, string $appointmentPdfPath, string $paymentPdfPath)
    {
        $this->appointment = $appointment;
        $this->appointmentPdfPath = $appointmentPdfPath;
        $this->paymentPdfPath = $paymentPdfPath;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Confirmación de Tu Cita en BlueyVet y Comprobante de Pago!',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appointments.confirmation', // Usaremos una plantilla Markdown
            with: [
                'user' => $this->appointment->user,
                'mascota' => $this->appointment->mascota,
                'service' => $this->appointment->service,
                'veterinarian' => $this->appointment->veterinarian->user,
                'appointment_date' => $this->appointment->start_time->format('d/m/Y'),
                'appointment_time' => $this->appointment->start_time->format('H:i A'),
                'payment_method' => $this->appointment->payment_method,
                'price' => $this->appointment->price,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if (file_exists($this->appointmentPdfPath)) {
            $attachments[] = Attachment::fromPath($this->appointmentPdfPath)
                                ->as('comprobante_cita_' . $this->appointment->id . '.pdf')
                                ->withMime('application/pdf');
        } else {
            Log::warning('PDF de cita no encontrado para adjuntar al correo: ' . $this->appointmentPdfPath);
        }

        if (file_exists($this->paymentPdfPath)) {
            $attachments[] = Attachment::fromPath($this->paymentPdfPath)
                                ->as('comprobante_pago_orden_' . $this->appointment->serviceOrder->id . '.pdf')
                                ->withMime('application/pdf');
        } else {
            Log::warning('PDF de pago no encontrado para adjuntar al correo: ' . $this->paymentPdfPath);
        }

        return $attachments;
    }
}