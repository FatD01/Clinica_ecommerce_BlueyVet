<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'parent_id', // ¡Asegúrate de que 'parent_id' esté en los fillable!
    ];

    /**
     * Relación de uno a muchos con Product.
     * Una categoría puede tener muchos productos.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relación para obtener la categoría padre.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Relación para obtener las categorías hijas.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Scope para obtener solo las categorías raíz (sin padre).
     */
    public function scopeRoot(Builder $query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Método para obtener el nombre completo de la categoría, incluyendo sus ancestros.
     * Ej: "Común - Veterinaria"
     */
    public function getFullPathAttribute()
    {
        $path = $this->name;
        $parent = $this->parent;

        while ($parent) {
            $path = $parent->name . ' - ' . $path;
            $parent = $parent->parent;
        }

        return $path;
    }

    /**
     * Carga recursivamente las relaciones 'children' de esta categoría hasta una profundidad específica.
     * Esto ayuda a evitar problemas N+1 al recorrer el árbol de categorías.
     *
     * @param int $depth La profundidad máxima de los hijos a cargar (ej. 5 es un buen valor inicial).
     * @return $this
     */
    
    public function loadRecursiveChildren(int $depth = 5)
    {
        if ($depth <= 0) {
            return $this;
        }

        // Carga los hijos directos si no están ya cargados
        $this->loadMissing('children');

        // Para cada hijo, carga sus propios hijos recursivamente
        $this->children->each(function ($child) use ($depth) {
            $child->loadRecursiveChildren($depth - 1);
        });

        return $this;
    }

    /**
     * Obtener todos los IDs de la categoría actual y sus subcategorías recursivamente.
     * NOTA: Este método devuelve el ID de la categoría actual + sus descendientes.
     * Para solo los descendientes, el método original era correcto.
     *
     * @return array
     */
    public function allDescendantIds(): array
    {
        $ids = [$this->id]; // Incluye el ID de la categoría actual

        // Carga los hijos si no están ya cargados para evitar N+1 queries aquí
        $this->loadMissing('children'); 

        foreach ($this->children as $child) {
            // Llama recursivamente a este mismo método para obtener los IDs de los hijos y sus descendientes
            $ids = array_merge($ids, $child->allDescendantIds()); 
        }

        return array_values(array_unique($ids)); // Asegura que no haya IDs duplicados y reindexa
    }
}