<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurnoMedico extends Model
{
    protected $table = 'turno_medico';
    protected $primaryKey = 'turId';

    protected $fillable = ['medId', 'turDiasLaborales', 'turHoraEntrada', 'turHoraSalida'];

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medId', 'medId');
    }
}