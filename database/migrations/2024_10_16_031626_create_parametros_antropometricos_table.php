<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParametrosAntropometricosTable extends Migration
{
    public function up()
    {
        Schema::create('parametros_antropometricos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade'); // Relación con el paciente
            $table->decimal('peso', 5, 2); // Peso con dos decimales
            $table->decimal('altura', 5, 2); // Altura con dos decimales
            $table->decimal('imc', 5, 2); // Índice de Masa Corporal con dos decimales
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parametros_antropometricos');
    }
}
