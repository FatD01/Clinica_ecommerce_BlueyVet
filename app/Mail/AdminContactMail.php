<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contactMessage; // Propiedad pública para acceder desde la vista

    /**
     * Create a new message instance.
     */
    public function __construct($contactMessage)
    {
        $this->contactMessage = $contactMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // El asunto del correo
        return new Envelope(
            subject: 'Nuevo Mensaje de Contacto en BlueyVet: ' . ($this->contactMessage->subject ?? 'Sin Asunto'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Define la vista Markdown que usará este correo
        return new Content(
            markdown: 'emails.contact.admin-notification',
            with: [ // Pasa la data del mensaje a la vista
                'contactMessage' => $this->contactMessage,
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
        return [];
    }
}