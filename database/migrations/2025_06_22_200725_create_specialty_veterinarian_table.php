<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialty_veterinarian', function (Blueprint $table) {
            $table->foreignId('specialty_id')->constrained()->onDelete('cascade');
            $table->foreignId('veterinarian_id')->constrained()->onDelete('cascade'); // Refiere a 'veterinarians.id'
            $table->primary(['specialty_id', 'veterinarian_id']); // Clave primaria compuesta
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialty_veterinarian');
    }
};