<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment; // *** IMPORTANTE: Asegúrate de importar Attachment ***

class ProductOrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $invoicePath; // *** Nueva propiedad para la ruta del PDF ***

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, ?string $invoicePath = null) // *** Acepta la ruta del PDF, puede ser null ***
    {
        $this->order = $order;
        $this->invoicePath = $invoicePath; // *** Asigna la ruta a la propiedad ***
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de tu Compra de Productos en BlueyVet',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'client.emails.product-order-confirmation',
            with: [
                'order' => $this->order,
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
        // *** Lógica para adjuntar el PDF ***
        if ($this->invoicePath && file_exists($this->invoicePath)) {
            return [
                Attachment::fromPath($this->invoicePath)
                    ->as('comprobante_compra_productos_' . $this->order->id . '.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        // Si no hay ruta o el archivo no existe, no adjuntes nada.
        // Un log de advertencia ya se maneja en el controlador si la generación falla.
        return [];
    }
}