<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenteMedico extends Model
{
    protected $table = 'asistente_medico';
    protected $primaryKey = 'astId';

    protected $fillable = [
        'user_id', 'astNombre', 'astApePat', 'astApeMat', 'astEdad',
        'astCorreo', 'astTelefono', 'astEstatus', 'astFechaReg'
    ];

    public function citas()
    {
        return $this->hasMany(Cita::class, 'astId', 'astId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
