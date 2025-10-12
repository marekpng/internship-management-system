<?php

namespace app\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Neplatný email alebo heslo.'
            ], 401);
        }

        // Firma musí mať aktívny účet
        if ($user->role === 'company' && !$user->company_account_active_state) {
            return response()->json([
                'message' => 'Váš firemný účet ešte nebol aktivovaný. Skontrolujte svoj email a potvrďte registráciu.'
            ], 403);
        }

        // Ak má používateľ povinnosť zmeniť heslo (napr. študent po prvom prihlásení)
        $mustChangePassword = $user->must_change_password;

        // Vygenerujeme nový token (Passport)
        $tokenResult = $user->createToken('API Token');
        $token = $tokenResult->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $tokenResult->token->expires_at,
            'must_change_password' => $mustChangePassword,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
            ],
        ]);
    }

    /**
     * Odhlásenie používateľa (zruší aktuálny token)
     */
    public function logout(Request $request)
    {
        // Získa aktuálny token používateľa
        $token = $request->user()->token();

        // Zneplatní ho
        $token->revoke();

        return response()->json([
            'message' => 'Boli ste úspešne odhlásený.'
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed', // musí byť "new_password_confirmation" v requeste
        ]);

        $user = $request->user();

        // Overenie starého hesla
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Neplatné aktuálne heslo.'
            ], 403);
        }

        // Aktualizácia hesla
        $user->password = Hash::make($request->new_password);
        $user->must_change_password = false; // už nemusí meniť heslo
        $user->save();

        return response()->json([
            'message' => 'Heslo bolo úspešne zmenené.'
        ]);
    }
}
