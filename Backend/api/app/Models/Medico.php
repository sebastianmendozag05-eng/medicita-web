<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    protected $table = 'medico';
    protected $primaryKey = 'medId';

    protected $fillable = [
        'user_id', 'medNombre', 'medApePat', 'medApeMat', 'medSexo',
        'medEdad', 'medCorreo', 'medCedula', 'medEstatus', 'medFechaReg'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'medId', 'medId');
    }

    public function turno()
    {
        return $this->hasOne(TurnoMedico::class, 'medId', 'medId');
    }
}