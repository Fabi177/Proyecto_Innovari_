<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AntecedentesFamiliares extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'hipertension',
        'diabetes',
        'cancer',
        'obesidad',
        'dislipidemia',
        'otros',
    ];

    public $table = 'antecedentes_familiares';

    // Relación con el modelo Paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}
