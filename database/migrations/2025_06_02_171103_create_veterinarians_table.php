<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veterinarians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Links to the user account
            $table->string('license_number')->unique()->nullable(); // Professional license/college number
            $table->string('specialty')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
            $table->softDeletes(); // ¡Añade esto!
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veterinarians');
    }
};
