<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AusenciaMedico extends Model
{
    protected $table = 'ausencia_medico';
    protected $primaryKey = 'ausId';

    protected $fillable = ['medId', 'ausTipo', 'ausFecha', 'ausMotivo'];

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medId', 'medId');
    }
}