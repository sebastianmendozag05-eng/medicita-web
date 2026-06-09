<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = 'paciente';
    protected $primaryKey = 'pacId';

    protected $fillable = [
        'pacNombre', 'pacApePat', 'pacApeMat', 'pacSexo',
        'pacFechaNac', 'pacNSS', 'pacCorreo', 'pacTelefono',
        'pacPeso', 'pacEstatura', 'pacEstatus', 'pacFechaReg'
    ];

    public function citas()
    {
        return $this->hasMany(Cita::class, 'pacId', 'pacId');
    }

    public function expediente()
    {
        return $this->hasOne(Expediente::class, 'pacId', 'pacId');
    }
}