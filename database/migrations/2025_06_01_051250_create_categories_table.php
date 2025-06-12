
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
           $table->id();
            $table->string('name', 100)->unique(); // Nombre de la categoría
            $table->text('description')->nullable(); // Descripción opcional
            // Nueva columna para la relación jerárquica (self-referencing)
            $table->foreignId('parent_id')
                  ->nullable() // Una categoría puede no tener padre (es una categoría raíz)
                  ->constrained('categories') // Se referencia a la misma tabla 'categories'
                  ->onDelete('cascade'); // Si una categoría padre se elimina, sus hijos también se eliminan (o 'set null' si prefieres que los hijos se conviertan en raíces)

            $table->timestamps();
             $table->softDeletes(); // ¡Añade esto!
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};