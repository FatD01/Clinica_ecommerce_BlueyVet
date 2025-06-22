<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification as LaravelNotification;
use Filament\Notifications\Notification as FilamentNotification;
// ¡IMPORTANTE! NO usamos Illuminate\Notifications\Messages\MailMessage aquí.
// ¡Usamos tu Mailable personalizado que tiene el método ->to() y la vista Blade!
use App\Mail\LowStockNotificationMail; // <-- ¡ASEGÚRATE DE QUE ESTA LÍNEA ESTÉ!
use Illuminate\Support\Facades\Log; // Para registrar errores

class LowStockNotification extends LaravelNotification implements ShouldQueue
{
    use Queueable;

    protected Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function via(object | string $notifiable): array
    {
        // Esto le dice a Laravel que puede enviar por base de datos (Filament) y por correo.
        return ['database', 'mail'];
    }

    public function toDatabase(object | string $notifiable): array
    {
        // Esta parte es para Filament, está PERFECTA como la tienes.
        return FilamentNotification::make()
            ->title('¡Stock Bajo: ' . $this->product->name . '!') //fea :>
            ->body('El producto "' . $this->product->name . '" tiene un stock bajo de ' . $this->product->stock . ' unidades (umbral: ' . $this->product->min_stock_threshold . ').')
            ->warning()
            ->icon('heroicon-o-exclamation-triangle')
            ->getDatabaseMessage();
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  object  $notifiable  // Este es el objeto User (administrador)
     * @return \Illuminate\Mail\Mailable // <-- ¡EL TIPO DE RETORNO AHORA ES TU MAILABLE!
     */
    public function toMail(object | string $notifiable): LowStockNotificationMail // <-- ¡AQUÍ ES DONDE CAMBIA EL TIPO A TU MAILABLE!
    {
        // Leemos directamente el correo de notificación del admin desde .env
        $adminNotificationEmail = env('ADMIN_EMAIL');

        // ¡IMPORTANTE! Verificar que el correo esté configurado y sea válido.
        if (! $adminNotificationEmail || ! filter_var($adminNotificationEmail, FILTER_VALIDATE_EMAIL)) {
            Log::error("ERROR DE CONFIGURACIÓN CRÍTICO: El correo 'ADMIN_EMAIL' no está configurado o es inválido en el archivo .env. No se pudo enviar la notificación de stock bajo para el producto: {$this->product->name}.");
            throw new \Exception("¡ERROR! Correo de administrador para alertas de stock bajo no configurado o inválido.");
        }

        // ¡AQUÍ ES DONDE INSTANCIAMOS TU MAILABLE PERSONALIZADO Y LE PASAMOS EL DESTINATARIO!
        // ¡LowStockNotificationMail SÍ TIENE EL MÉTODO ->to()!
        return (new LowStockNotificationMail($this->product))
                    ->to($adminNotificationEmail); // <-- ¡ESTA LÍNEA YA NO DARÁ ERROR!
    }

    public function toArray(object | string $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'current_stock' => $this->product->stock,
            'threshold' => $this->product->min_stock_threshold,
        ];
    }
}