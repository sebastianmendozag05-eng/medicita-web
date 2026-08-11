<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'faq';
    protected $primaryKey = 'faqId';

    protected $fillable = [
        'faqCategoria', 'faqPregunta', 'faqRespuesta', 'faqPalabrasClave', 'faqActivo',
    ];

    protected $casts = ['faqActivo' => 'boolean'];
}
