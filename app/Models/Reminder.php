<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id', // ¡Cambio aquí!
        'mascota_id',
        'related_to_type',
        'related_to_id',
        'title',
        'description',
        'remind_at',
        'sent_at',
        'is_active',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'sent_at'   => 'datetime',
        'is_active' => 'boolean',
    ];

   protected $appends = ['formatted_reminder_text']; 

    // Relación con el Cliente (dueño del recordatorio)
    public function cliente() // Cambio de 'user()' a 'cliente()'
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación con la mascota
    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    // Relación polimórfica (opcional)
    public function relatedTo()
    {
        return $this->morphTo();
    }

    // Accessor para el texto del recordatorio dinámico
    public function getFormattedReminderTextAttribute()
    {
         if (!$this->relationLoaded('mascota')) {
        $this->load('mascota');
    }
    if (!$this->mascota) {
        return "El recordatorio de **{$this->title}** está programado para el {$this->remind_at->format('d/m/Y H:i A')}. (Mascota no encontrada)";
    }

    // Usamos una zona horaria específica para evitar inconsistencias
    $now = Carbon::now(new \DateTimeZone('America/Lima'));
    $remindAt = $this->remind_at->copy()->setTimezone('America/Lima');

    // Si el recordatorio ya pasó
    if ($remindAt->isPast() && !$remindAt->isToday()) {
        return "El recordatorio de **{$this->title}** para **{$this->mascota->name}** fue el {$remindAt->format('d/m/Y H:i A')}.";
    }
    // Si es hoy, verificamos si ya pasó la hora o si es en el futuro de hoy
    if ($remindAt->isToday()) {
        if ($remindAt->isPast()) {
            return "El recordatorio de **{$this->title}** para **{$this->mascota->name}** fue hoy a las {$remindAt->format('H:i A')}.";
        }
        return "¡Hoy es el recordatorio de **{$this->title}** para **{$this->mascota->name}** a las {$remindAt->format('H:i A')}!";
    }
    // Si es mañana
    if ($remindAt->isTomorrow()) {
        return "El recordatorio de **{$this->title}** para **{$this->mascota->name}** es mañana.";
    }

    // Calcula la diferencia en horas y luego redondea hacia arriba a días.
    // Esto asegura que 24.1 horas se cuente como 2 días.
    $diffInHours = $now->diffInHours($remindAt, false); // `false` para obtener diferencia con signo si es pasado, aunque ya lo filtramos

    // Solo calculamos días si el recordatorio es futuro
    if ($diffInHours > 0) {
        $diffInDays = (int) ceil($diffInHours / 24); // Redondea hacia arriba y convierte a entero
        return "El recordatorio de **{$this->title}** para **{$this->mascota->name}** es en {$diffInDays} día(s).";
    }

    // Fallback para cualquier otro caso inesperado (debería ser cubierto por lo anterior)
    return "El recordatorio de **{$this->title}** para **{$this->mascota->name}** está programado para el {$remindAt->format('d/m/Y H:i A')}.";
}
}