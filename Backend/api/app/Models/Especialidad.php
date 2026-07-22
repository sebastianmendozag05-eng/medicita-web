<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table = 'especialidad';
    protected $primaryKey = 'espeId';

    protected $fillable = ['espeNombre'];

    public function medicos()
    {
        return $this->belongsToMany(Medico::class, 'medico_especialidad', 'espeId', 'medId');
    }
}
