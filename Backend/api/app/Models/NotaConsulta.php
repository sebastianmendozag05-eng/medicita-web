<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaConsulta extends Model
{
    protected $table = 'nota_consulta';
    protected $primaryKey = 'notaId';

    protected $fillable = [
        'citId', 'medId', 'pacId', 'notaAsistencia',
        'notaDiagnostico', 'notaReceta', 'notaFechaCreacion'
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'citId', 'citId');
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medId', 'medId');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'pacId', 'pacId');
    }
}