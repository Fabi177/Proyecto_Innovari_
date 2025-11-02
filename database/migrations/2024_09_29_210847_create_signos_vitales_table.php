<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignosVitalesTable extends Migration
{
    public function up()
    {
        Schema::create('signos_vitales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade'); // Relación con el paciente
            $table->decimal('presion_arterial', 5, 2); // Presión arterial
            $table->integer('frecuencia_cardiaca'); // Frecuencia cardíaca
            $table->integer('frecuencia_respiratoria'); // Frecuencia respiratoria
            $table->decimal('temperatura', 5, 2); // Temperatura
            $table->decimal('saturacion_oxigeno', 5, 2); // Saturación de oxígeno
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('signos_vitales');
    }
}
