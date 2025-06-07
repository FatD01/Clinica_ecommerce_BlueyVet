<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            // Foreign key to your existing 'mascotas' table
            $table->foreignId('mascota_id')->constrained('mascotas')->onDelete('cascade');
            // Foreign key to the newly created 'veterinarians' table
            $table->foreignId('veterinarian_id')->constrained('veterinarians')->onDelete('restrict');
            // Assuming you have a 'services' table, link to it (optional)
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');

            $table->timestamp('consultation_date')->useCurrent(); // Date and time of the consultation
            $table->text('reason_for_consultation')->nullable(); // Reason for the visit
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('notes')->nullable(); // General notes/observations

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};