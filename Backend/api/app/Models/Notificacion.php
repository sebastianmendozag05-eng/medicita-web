<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificacion';
    protected $primaryKey = 'notifId';

    protected $fillable = ['user_id', 'tipo', 'titulo', 'descripcion', 'leida'];

    protected $casts = ['leida' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Crea una notificación para el usuario vinculado a un registro de dominio
     * (paciente/medico/asistente_medico), si tiene cuenta enlazada.
     */
    public static function notificarA($domainModel, string $tipo, string $titulo, ?string $descripcion = null): void
    {
        if (!$domainModel || !$domainModel->user_id) return;

        self::create([
            'user_id'     => $domainModel->user_id,
            'tipo'        => $tipo,
            'titulo'      => $titulo,
            'descripcion' => $descripcion,
        ]);
    }
}
