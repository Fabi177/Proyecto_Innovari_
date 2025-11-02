<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametrosAntropometricos extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'peso',
        'altura',
        'imc',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}
