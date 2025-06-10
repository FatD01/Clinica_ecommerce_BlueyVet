<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\ServiceOrder; // Importa tu modelo ServiceOrder

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var \App\Models\ServiceOrder
     */
    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct(ServiceOrder $order) // El constructor recibe la orden
    {
        $this->order = $order; // Asigna la orden a una propiedad pública
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de tu Compra en BlueyVet', // Asunto del correo
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            // markdown: 'emails.orders.confirmation', // Ruta a tu plantilla Markdown de correo
            // Cambia 'markdown:' a 'html:'
            html: 'emails.order-confirmation', // La vista Blade HTML plano
            with: [
                'order' => $this->order, // Pasa la instancia de la orden a la vista
                'total' => number_format($this->order->total, 2), // Ejemplo de datos adicionales
                'orderId' => $this->order->id,
                'clientName' => $this->order->user->name ?? 'Cliente', // Accede al nombre del cliente
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