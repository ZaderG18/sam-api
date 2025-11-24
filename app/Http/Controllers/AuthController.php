<?

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller {
    public function login(Request $request){
        $credenciais = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($credenciais)){
            return response()->json([
                'mensagem' => 'Login realizado com sucesso!',
                'token' => $token,
                'user' => $user
            ]);
        }
    }
}