<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. LIMPEZA DE ENUMS (O Segredo para não dar erro no migrate:fresh)
        // O CASCADE garante que o banco apague o tipo mesmo que ele esteja em uso por uma tabela antiga
        DB::statement("DROP TYPE IF EXISTS tipo_usuario CASCADE");
        DB::statement("DROP TYPE IF EXISTS status_assinatura CASCADE");
        DB::statement("DROP TYPE IF EXISTS status_matricula CASCADE");
        DB::statement("DROP TYPE IF EXISTS tipo_recurso CASCADE");
        DB::statement("DROP TYPE IF EXISTS status_reserva CASCADE");
        DB::statement("DROP TYPE IF EXISTS status_pagamento CASCADE");
        DB::statement("DROP TYPE IF EXISTS turno_turma CASCADE");

        // 2. CRIAR OS ENUMS
        DB::statement("CREATE TYPE tipo_usuario AS ENUM ('admin_saas', 'diretor', 'coordenador', 'professor', 'aluno', 'responsavel')");
        DB::statement("CREATE TYPE status_assinatura AS ENUM ('ativa', 'inadimplente', 'cancelada', 'trial')");
        DB::statement("CREATE TYPE status_matricula AS ENUM ('ativo', 'trancado', 'formado', 'transferido')");
        DB::statement("CREATE TYPE tipo_recurso AS ENUM ('sala', 'laboratorio', 'quadra', 'auditorio', 'equipamento')");
        DB::statement("CREATE TYPE status_reserva AS ENUM ('pendente', 'aprovada', 'rejeitada', 'cancelada')");
        DB::statement("CREATE TYPE status_pagamento AS ENUM ('pendente', 'pago', 'atrasado', 'cancelado')");
        DB::statement("CREATE TYPE turno_turma AS ENUM ('manha', 'tarde', 'noite', 'integral')");

        // 3. MÓDULO SAAS (B2B)
        Schema::create('planos_assinatura', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); 
            $table->decimal('valor_mensal', 10, 2);
            $table->integer('limite_alunos')->nullable();
            $table->jsonb('funcionalidades')->nullable();
            $table->timestamps();
        });

        Schema::create('instituicoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome_fantasia');
            $table->string('razao_social')->nullable();
            $table->string('cnpj')->unique()->nullable();
            $table->string('subdominio')->unique(); 
            $table->string('logo_url')->nullable();
            $table->string('cor_primaria')->default('#6D28D9');
            
            $table->foreignId('id_plano')->constrained('planos_assinatura');
            $table->timestamps();
        });
        // Adiciona a coluna usando o tipo ENUM criado acima
        DB::statement("ALTER TABLE instituicoes ADD COLUMN status status_assinatura DEFAULT 'trial'");


        // 4. MÓDULO DE USUÁRIOS (Perfis)
        Schema::create('perfis', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            
            $table->foreignUuid('id_instituicao')->constrained('instituicoes')->onDelete('cascade');
            $table->string('nome_completo');
            $table->string('email');
            $table->string('foto_url')->nullable();
            $table->string('telefone')->nullable();
            $table->timestamps();

            $table->unique(['email', 'id_instituicao']);
        });
        DB::statement("ALTER TABLE perfis ADD COLUMN tipo tipo_usuario NOT NULL");


        // Tabelas de Detalhes (1:1 com Perfis)
        Schema::create('alunos', function (Blueprint $table) {
            $table->uuid('id_perfil')->primary()->constrained('perfis')->onDelete('cascade');
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->string('rm'); 
            $table->date('data_nascimento')->nullable();
            $table->string('cpf')->nullable();
        });

        Schema::create('professores', function (Blueprint $table) {
            $table->uuid('id_perfil')->primary()->constrained('perfis')->onDelete('cascade');
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->string('registro_funcional')->nullable();
            $table->string('formacao')->nullable();
        });

        Schema::create('responsaveis', function (Blueprint $table) {
            $table->uuid('id_perfil')->primary()->constrained('perfis')->onDelete('cascade');
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->string('cpf');
            $table->text('endereco')->nullable();
        });

        Schema::create('aluno_responsavel', function (Blueprint $table) {
            $table->foreignUuid('id_aluno')->constrained('alunos', 'id_perfil');
            $table->foreignUuid('id_responsavel')->constrained('responsaveis', 'id_perfil');
            $table->string('parentesco')->nullable();
            $table->boolean('financeiro')->default(false);
            $table->primary(['id_aluno', 'id_responsavel']);
        });


        // 5. MÓDULO ACADÊMICO
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->string('nome');
            $table->string('sigla')->nullable();
            $table->text('descricao')->nullable();
            $table->timestamps();
        });

        Schema::create('disciplinas', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->string('nome');
            $table->string('sigla')->nullable();
            $table->timestamps();
        });

        Schema::create('turmas', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->foreignId('id_curso')->constrained('cursos');
            $table->string('nome');
            $table->integer('ano_letivo');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE turmas ADD COLUMN turno turno_turma NOT NULL");

        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->foreignUuid('id_aluno')->constrained('alunos', 'id_perfil');
            $table->foreignId('id_turma')->constrained('turmas');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE matriculas ADD COLUMN status status_matricula DEFAULT 'ativo'");

        Schema::create('grade_aulas', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->foreignId('id_turma')->constrained('turmas');
            $table->foreignId('id_disciplina')->constrained('disciplinas');
            $table->foreignUuid('id_professor')->constrained('professores', 'id_perfil');
            $table->integer('dia_semana');
            $table->time('horario_inicio');
            $table->time('horario_fim');
        });


        // 6. MÓDULO PEDAGÓGICO
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->foreignId('id_matricula')->constrained('matriculas');
            $table->foreignId('id_disciplina')->constrained('disciplinas');
            $table->decimal('valor', 4, 2);
            $table->integer('bimestre');
            $table->string('tipo_avaliacao'); 
            $table->timestamp('data_lancamento')->useCurrent();
        });

        Schema::create('frequencias', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->foreignId('id_matricula')->constrained('matriculas');
            $table->foreignId('id_disciplina')->constrained('disciplinas');
            $table->date('data_aula');
            $table->boolean('presente')->default(true);
            $table->text('observacao')->nullable();
        });

        Schema::create('atividades', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->foreignId('id_turma')->constrained('turmas');
            $table->foreignUuid('id_professor')->constrained('professores', 'id_perfil');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->timestamp('data_entrega')->nullable();
            $table->string('arquivo_anexo_url')->nullable();
            $table->timestamps();
        });


        // 7. MÓDULOS NOVOS (Reservas & Almoxarifado)
        Schema::create('recursos', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->string('nome');
            $table->integer('capacidade')->nullable();
            $table->boolean('ativo')->default(true);
        });
        DB::statement("ALTER TABLE recursos ADD COLUMN tipo tipo_recurso NOT NULL");

        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->foreignId('id_recurso')->constrained('recursos');
            $table->foreignUuid('id_solicitante')->constrained('perfis');
            $table->timestamp('data_inicio');
            $table->timestamp('data_fim');
            $table->text('motivo')->nullable();
        });
        DB::statement("ALTER TABLE reservas ADD COLUMN status status_reserva DEFAULT 'pendente'");

        Schema::create('itens_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->string('nome');
            $table->integer('quantidade_atual')->default(0);
            $table->string('tipo')->nullable(); 
        });

        Schema::create('movimentacoes_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->foreignId('id_item')->constrained('itens_estoque');
            $table->foreignUuid('id_aluno')->nullable()->constrained('alunos', 'id_perfil');
            $table->integer('quantidade');
            $table->string('tipo_movimento'); 
            $table->timestamp('data_movimento')->useCurrent();
        });


        // 8. MÓDULO FINANCEIRO (B2C)
        Schema::create('config_pagamentos', function (Blueprint $table) {
            $table->uuid('id_instituicao')->primary()->constrained('instituicoes');
            $table->string('gateway_provider'); 
            $table->string('public_key')->nullable();
            $table->string('secret_key')->nullable();
        });

        Schema::create('cobrancas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_instituicao')->constrained('instituicoes');
            $table->foreignUuid('id_aluno')->constrained('alunos', 'id_perfil');
            $table->foreignUuid('id_responsavel_financeiro')->nullable()->constrained('responsaveis', 'id_perfil');
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->string('link_pagamento')->nullable();
            $table->string('id_transacao_externa')->nullable();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE cobrancas ADD COLUMN status status_pagamento DEFAULT 'pendente'");
    }

    public function down(): void
    {
        // A ordem de drop deve ser inversa à de criação por causa das FKs
        Schema::dropIfExists('cobrancas');
        Schema::dropIfExists('config_pagamentos');
        Schema::dropIfExists('movimentacoes_estoque');
        Schema::dropIfExists('itens_estoque');
        Schema::dropIfExists('reservas');
        Schema::dropIfExists('recursos');
        Schema::dropIfExists('atividades');
        Schema::dropIfExists('frequencias');
        Schema::dropIfExists('notas');
        Schema::dropIfExists('grade_aulas');
        Schema::dropIfExists('matriculas');
        Schema::dropIfExists('turmas');
        Schema::dropIfExists('disciplinas');
        Schema::dropIfExists('cursos');
        Schema::dropIfExists('aluno_responsavel');
        Schema::dropIfExists('responsaveis');
        Schema::dropIfExists('professores');
        Schema::dropIfExists('alunos');
        Schema::dropIfExists('perfis');
        Schema::dropIfExists('instituicoes');
        Schema::dropIfExists('planos_assinatura');

        // Drop Types (Só apague se não estiver usando em outro lugar)
        DB::statement("DROP TYPE IF EXISTS turno_turma CASCADE");
        DB::statement("DROP TYPE IF EXISTS status_pagamento CASCADE");
        DB::statement("DROP TYPE IF EXISTS status_reserva CASCADE");
        DB::statement("DROP TYPE IF EXISTS tipo_recurso CASCADE");
        DB::statement("DROP TYPE IF EXISTS status_matricula CASCADE");
        DB::statement("DROP TYPE IF EXISTS status_assinatura CASCADE");
        DB::statement("DROP TYPE IF EXISTS tipo_usuario CASCADE");
    }
};