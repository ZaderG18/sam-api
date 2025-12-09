<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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
| API Routes - VERSÃO FINAL SEGURA
|--------------------------------------------------------------------------
*/

// === ROTAS PÚBLICAS ===
// A única coisa que alguém de fora pode fazer é tentar logar
Route::post('/login', [AuthController::class, 'login']);


// === ROTAS PROTEGIDAS (Exigem Login + Token) ===
Route::middleware('auth:sanctum')->group(function () {

    // Endpoint para o Frontend saber quem está logado
    Route::get('/me', function (\Illuminate\Http\Request $request) {
        return $request->user()->load('perfil', 'instituicao');
    });

    // 1. Rotas ADMIN SAAS (Só desenvolvedores)
    Route::middleware('role:admin_saas')->group(function () {
        Route::post('/instituicoes', [InstituicaoController::class, 'store']); 
        Route::get('/faturamento', [SaaSController::class, 'faturamento']); 
    });

    // 2. Rotas DIRETOR (Dono da Escola)
    Route::middleware('role:diretor')->group(function () {
        // SEGURANÇA RESTABELECIDA: Só diretor logado cria usuários
        Route::post('/usuarios', [UserController::class, 'store']); 
        Route::post('/cursos', [CursoController::class, 'store']);
        Route::get('/financeiro/inadimplencia', [FinanceiroController::class, 'inadimplencia']);
    });

    // 3. Rotas PROFESSOR
    Route::middleware('role:professor')->group(function () {
        Route::post('/chamada', [ChamadaController::class, 'store']);
        Route::post('/notas', [NotaController::class, 'store']);
    });

    // 4. Rotas ALUNO
    Route::middleware('role:aluno')->group(function () {
        Route::get('/boletim', [BoletimController::class, 'index']);
    });
    
    // 5. Rotas RESPONSÁVEL
    Route::middleware('role:responsavel')->group(function () {
        Route::get('/mensalidades', [FinanceiroController::class, 'mensalidades']);
        Route::post('/pagar', [PagamentoController::class, 'pagar']);
    });

    // 6. Rotas COMUNS
    Route::middleware('role:diretor,coordenador')->group(function () {
        Route::get('/turmas', [TurmaController::class, 'index']);
    });

});