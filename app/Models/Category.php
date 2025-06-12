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
     * Obtener todos los IDs de subcategorías recursivamente.
     */
    public function allDescendantIds()
    {
        $ids = collect();

        foreach ($this->children as $child) {
            $ids->push($child->id);
            $ids = $ids->merge($child->allDescendantIds());
        }

        return $ids;
    }
}
