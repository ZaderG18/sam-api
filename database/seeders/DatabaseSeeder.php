<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User; // Importante para usar o Model

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Criar um Plano de Assinatura (A escola precisa de um plano)
        $planoId = DB::table('planos_assinatura')->insertGetId([
            'nome' => 'Plano Gold (Dev)',
            'valor_mensal' => 99.90,
            'limite_alunos' => 500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Criar a Instituição (Escola)
        $escolaId = (string) Str::uuid(); // Gera um ID único

        DB::table('instituicoes')->insert([
            'id' => $escolaId,
            'id_plano' => $planoId,
            'nome_fantasia' => 'Escola Modelo SAM',
            'razao_social' => 'SAM Educacional Ltda',
            'cnpj' => '12.345.678/0001-99',
            'subdominio' => 'demo',
            'status' => 'ativa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. CRIAR O DIRETOR (Aqui está a mágica!)
        // Usamos o Model User para ele encriptar a senha corretamente
        User::create([
            'name' => 'Diretor Admin',
            'email' => 'diretor@sam.com', // LOGIN
            'password' => Hash::make('12345678'), // SENHA
            'id_instituicao' => $escolaId, // Vincula à escola criada acima
            'tipo_usuario' => 'diretor', // Define que é o chefe
            'telefone' => '(11) 99999-8888',
            'foto_perfil' => null,
        ]);

        $this->command->info("---------------------------------------");
        $this->command->info("✅ AMBIENTE CRIADO COM SUCESSO!");
        $this->command->info("---------------------------------------");
        $this->command->info("🏫 Escola ID: {$escolaId}");
        $this->command->info("👤 Login: diretor@sam.com");
        $this->command->info("🔑 Senha: 12345678");
        $this->command->info("---------------------------------------");
    }
}