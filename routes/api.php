<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// Importe TODOS os controllers que vamos criar (mesmo que não existam ainda, o erro mudará para "Class not found", o que é um progresso)
use App\Http\Controllers\Api\InstituicaoController;
use App\Http\Controllers\Api\SaaSController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CursoController;
use App\Http\Controllers\Api\FinanceiroController;
use App\Http\Controllers\Api\ChamadaController;
use App\Http\Controllers\Api\NotaController;
use App\Http\Controllers\Api\BoletimController;
use App\Http\Controllers\Api\PagamentoController;
use App\Http\Controllers\Api\TurmaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rotas Públicas
Route::post('/login', [AuthController::class, 'login']);

// Rotas Protegidas (Precisa estar logado com Token Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // Endpoint para verificar quem está logado (Útil para o Frontend)
    Route::get('/me', function (\Illuminate\Http\Request $request) {
        return $request->user()->load('perfil', 'instituicao');
    });

    // 1. Rotas que SÓ O DESENVOLVEDOR (Admin SaaS) acessa
    Route::middleware('role:admin_saas')->group(function () {
        Route::post('/instituicoes', [InstituicaoController::class, 'store']); 
        Route::get('/faturamento', [SaaSController::class, 'faturamento']); 
    });

    // 2. Rotas que DIRETOR acessa
    Route::middleware('role:diretor')->group(function () {
        Route::post('/usuarios', [UserController::class, 'store']); 
        Route::post('/cursos', [CursoController::class, 'store']);
        Route::get('/financeiro/inadimplencia', [FinanceiroController::class, 'inadimplencia']);
    });

    // 3. Rotas que PROFESSOR acessa
    Route::middleware('role:professor')->group(function () {
        Route::post('/chamada', [ChamadaController::class, 'store']);
        Route::post('/notas', [NotaController::class, 'store']);
    });

    // 4. Rotas que ALUNO acessa
    Route::middleware('role:aluno')->group(function () {
        Route::get('/boletim', [BoletimController::class, 'index']);
    });
    
    // 5. Rotas que RESPONSÁVEL acessa
    Route::middleware('role:responsavel')->group(function () {
        Route::get('/mensalidades', [FinanceiroController::class, 'mensalidades']);
        Route::post('/pagar', [PagamentoController::class, 'pagar']);
    });

    // 6. Rotas Comuns (Diretor e Coordenador)
    Route::middleware('role:diretor,coordenador')->group(function () {
        Route::get('/turmas', [TurmaController::class, 'index']);
    });

});