<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Si usaste softDeletes

class Post extends Model
{
    use HasFactory, SoftDeletes; // Agrega SoftDeletes si lo usaste

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'image_path',
        'category',
        'is_published',
        'published_at',
        'type', // Asegúrate de agregarlo aquí
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // Relación con el usuario que lo publicó (si applies)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}