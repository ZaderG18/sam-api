<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController; // Importante: Verifique se AuthController está nesta pasta ou em Api/
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

// === ROTAS PÚBLICAS (Abertas para teste) ===

Route::post('/login', [AuthController::class, 'login']);

// [TEMPORÁRIO] Rota de cadastro aberta para você testar o formulário sem estar logado
Route::post('/usuarios', [UserController::class, 'store']); 


// === ROTAS PROTEGIDAS (Exigem Login + Token) ===
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', function (\Illuminate\Http\Request $request) {
        return $request->user()->load('perfil', 'instituicao');
    });

    // 1. Rotas ADMIN SAAS
    Route::middleware('role:admin_saas')->group(function () {
        Route::post('/instituicoes', [InstituicaoController::class, 'store']); 
        Route::get('/faturamento', [SaaSController::class, 'faturamento']); 
    });

    // 2. Rotas DIRETOR
    Route::middleware('role:diretor')->group(function () {
        // Route::post('/usuarios', [UserController::class, 'store']); // <--- Comentei aqui e movi para cima
        Route::post('/cursos', [CursoController::class, 'store']);
        Route::get('/financeiro/inadimplencia', [FinanceiroController::class, 'inadimplencia']);
    });

    // ... (Demais rotas mantidas iguais) ...
    
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