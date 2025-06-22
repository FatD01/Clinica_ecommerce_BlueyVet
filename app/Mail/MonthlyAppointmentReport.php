<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment; // Importar Attachment
use Illuminate\Queue\SerializesModels;

class MonthlyAppointmentReport extends Mailable
{
    use Queueable, SerializesModels;

    public $monthName;
    public $year;
    public $attachmentPath;
    public $attachmentName;

    /**
     * Create a new message instance.
     */
    public function __construct($monthName, $year, $attachmentPath, $attachmentName)
    {
        $this->monthName = $monthName;
        $this->year = $year;
        $this->attachmentPath = $attachmentPath;
        $this->attachmentName = $attachmentName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reporte Mensual de Citas - {$this->monthName} {$this->year}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.monthly-appointment-report',
            with: [
                'monthName' => $this->monthName,
                'year' => $this->year,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->attachmentPath)
                ->as($this->attachmentName)
                ->withMime('application/pdf'),
        ];
    }
}