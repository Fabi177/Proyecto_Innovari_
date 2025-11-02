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
    Schema::create('antecedentes_familiares', function (Blueprint $table) {
        $table->id();
        $table->foreignId('paciente_id')->constrained('pacientes');
        $table->boolean('hipertension')->default(false);
        $table->boolean('diabetes')->default(false);
        $table->string('cancer')->nullable();
        $table->boolean('obesidad')->default(false);
        $table->boolean('dislipidemia')->default(false);
        $table->text('otros')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antecedentes_familiares');
    }
};
