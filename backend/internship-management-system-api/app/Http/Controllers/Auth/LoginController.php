<?php

namespace app\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        $roles = $user->roles->pluck('name')->toArray();

        // Firma musí mať aktívny účet
        if (in_array('company', $roles) && !$user->company_account_active_state) {
            return response()->json([
                'message' => 'Váš firemný účet ešte nebol aktivovaný. Skontrolujte svoj email a potvrďte registráciu.'
            ], 403);
        }

        // 🔹 Nové – kontrola, či používateľ musí zmeniť heslo
        if ($user->must_change_password) {
            return response()->json([
                'status' => 'FORCE_PASSWORD_CHANGE',
                'message' => 'Musíte zmeniť svoje heslo pred pokračovaním.',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name'),
                ],
            ], 403);
        }

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
     * Odhlásenie používateľa
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

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Neplatné aktuálne heslo.'
            ], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->must_change_password = false; // 🔹 reset flagu po úspešnej zmene
        $user->save();

        return response()->json([
            'message' => 'Heslo bolo úspešne zmenené.'
        ]);
    }

    // --- ZABUDLI STE HESLO ---
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        // if (str_ends_with($email, '@student.ukf.sk')) {
        //     return response()->json(['message' => 'Alternatívne študentské emaily nie sú podporované.'], 400);
        // }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'Používateľ s týmto emailom neexistuje.'], 404);
        }

        $token = Str::random(60);

        // Uloženie tokenu do password_resets tabuľky
        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );
        $url = url("/reset-password?token={$token}&email={$email}");

        Mail::to($email)->send(new ResetPasswordMail($url));

        return response()->json(['message' => 'Na váš email bol odoslaný link na obnovenie hesla.']);
    }

    // --- RESET HESLA ---
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $record = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json(['message' => 'Neplatný alebo expirovaný token.'], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Používateľ neexistuje.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Vymažeme reset záznam
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Heslo bolo úspešne obnovené.']);
    }
}
