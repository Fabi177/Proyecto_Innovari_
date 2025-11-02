<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intervencion extends Model
{
    // Relación inversa con Paciente (muchos a uno)
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}

