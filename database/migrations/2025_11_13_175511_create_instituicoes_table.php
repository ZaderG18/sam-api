<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('instituicoes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instituicoes');

    }

};
$table->foreignId('id_plano')->constrained('planos_assinatura');
$table->string('nome_fantasia');
$table->string('cnpj')->unique();
$table->string('subdominio')->unique();
$table->enum('status_assinatura', ['ativa', 'inadimplente', 'cancelada'])->default('ativa');
// ... outros campos do diagrama
