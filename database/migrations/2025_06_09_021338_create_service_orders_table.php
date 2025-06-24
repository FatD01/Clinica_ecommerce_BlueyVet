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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            // user_id ahora es REQUIRED (no nullable)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // El cliente que compra (SIEMPRE logueado)
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade'); // El servicio comprado
            $table->decimal('amount', 8, 2); // Precio al momento de la compra
            $table->string('paypal_order_id')->nullable()->unique(); // ID de la orden de PayPal
            $table->string('status')->default('pending'); // Estado del pago: pending, completed, failed, refunded
            $table->json('payment_details')->nullable(); // Detalles completos de la transacción de PayPal (JSON)
            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // Para 'soft delete' si lo necesitas (deleted_at)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};