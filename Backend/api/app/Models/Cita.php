<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'cita';
    protected $primaryKey = 'citId';

    protected $fillable = [
        'medId', 'pacId', 'astId', 'citFecha', 'citHora',
        'citMotivo', 'citEstatus', 'citFechaReg', 'citMotivoCancela'
    ];

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medId', 'medId');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'pacId', 'pacId');
    }
}