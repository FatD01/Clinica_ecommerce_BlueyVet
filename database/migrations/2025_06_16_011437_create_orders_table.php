<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Si el pedido puede ser de un invitado, usa nullable
            $table->string('paypal_order_id')->nullable()->unique(); // ID de la orden de PayPal
            $table->string('paypal_payment_id')->nullable(); // ID de la transacción de captura de PayPal (opcional)
            $table->decimal('total_amount', 10, 2); // Monto total del pedido en tu moneda local
            $table->string('currency', 3); // La moneda del total_amount (ej. PEN)
            $table->string('status')->default('pending'); // pending, completed, cancelled, failed, refunded
            $table->json('payment_details')->nullable(); // Almacenar la respuesta JSON de PayPal
            $table->timestamps();
            $table->softDeletes(); // Para borrado suave
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};