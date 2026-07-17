<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = 'paciente';
    protected $primaryKey = 'pacId';

    protected $fillable = [
        'user_id', 'pacNombre', 'pacApePat', 'pacApeMat', 'pacSexo',
        'pacFechaNac', 'pacNSS', 'pacCorreo', 'pacTelefono',
        'pacPeso', 'pacEstatura', 'pacEstatus', 'pacFechaReg'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'pacId', 'pacId');
    }

    public function expediente()
    {
        return $this->hasOne(Expediente::class, 'pacId', 'pacId');
    }
}