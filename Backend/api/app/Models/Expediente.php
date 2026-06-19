<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expediente extends Model
{
    protected $table = 'expediente';
    protected $primaryKey = 'expId';

    protected $fillable = ['pacId', 'expAlergias', 'expPadecimientos', 'expMedicamentos'];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'pacId', 'pacId');
    }
}