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
    Schema::create('antecedentes_personales', function (Blueprint $table) {
        $table->id();
        $table->foreignId('paciente_id')->constrained('pacientes');
        $table->boolean('hipertension')->default(false);
        $table->boolean('diabetes_tipo_1')->default(false);
        $table->boolean('cancer')->default(false);
        $table->boolean('dislipidemia')->default(false);
        $table->boolean('obesidad')->default(false);
        $table->string('alergias')->nullable();
        $table->boolean('embarazo')->default(false);
        $table->text('cirugias_previas')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antecedentes_personales');
    }
};
