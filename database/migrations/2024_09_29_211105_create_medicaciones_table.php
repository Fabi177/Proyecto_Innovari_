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
    Schema::create('medicaciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('paciente_id')->constrained('pacientes');
        $table->string('nombre_medicacion');
        $table->text('dosis')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicaciones');
    }
};
