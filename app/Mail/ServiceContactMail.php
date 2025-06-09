<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data; // Aquí guardaremos los datos del formulario

    /**
     * Create a new message instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Solicitud de Cita por Servicio - BlueyVet',
            // El replyTo es importante para que puedas responder directamente al cliente
            replyTo: [
                new \Illuminate\Mail\Mailables\Address($this->data['email'], $this->data['nombres'] . ' ' . $this->data['apellidos']),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.services.contact', // La vista Blade para el correo
            with: ['formData' => $this->data], // Pasa los datos a la vista
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