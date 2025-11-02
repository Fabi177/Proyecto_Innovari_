<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('pacientes', function (Blueprint $table) {
        $table->id();
        $table->string('nombre_completo');
        $table->string('apellido');
        $table->date('fecha_nacimiento');
        $table->integer('edad');
        $table->string('sexo');
        $table->string('estado_civil');
        $table->string('lugar_origen');
        $table->string('nivel_estudio');
        $table->string('ocupacion');
        $table->integer('anos_puesto');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
