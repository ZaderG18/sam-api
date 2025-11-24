<?
use App\Http\Controllers\AuthController;
use Symfony\Component\Routing\Route;

Route::post('/auth/login', [AuthController::class, 'login']);