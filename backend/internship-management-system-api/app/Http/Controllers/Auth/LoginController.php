<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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

        // Základné údaje, ktoré potrebujeme mať k dispozícii hneď po logine (pre všetky roly)
        // Poznámka: tieto polia sa používajú na zobrazenie mena v navbare a na predvyplnenie nastavení.
        $baseUserPayload = [
            'id' => $user->id,
            'email' => $user->email,
            'roles' => $roles,

            // Profilové údaje (fungujú pre študenta aj garanta; firma ich môže mať prázdne)
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
            'alternative_email' => $user->alternative_email,

            // Notifikačné nastavenia (používajú sa v Settings a pri emailových notifikáciách)
            'notify_new_request' => (bool) $user->notify_new_request,
            'notify_approved' => (bool) $user->notify_approved,
            'notify_rejected' => (bool) $user->notify_rejected,
            'notify_profile_change' => (bool) $user->notify_profile_change,
        ];

        // Firemné údaje ponechávame len pre rolu company (aby sme garantovi/študentovi neposielali company polia)
        $companyPayload = [];
        if (in_array('company', $roles)) {
            $companyPayload = [
                'company_name' => $user->company_name,
                'contact_person_name' => $user->contact_person_name,
                'contact_person_email' => $user->contact_person_email,
                'contact_person_phone' => $user->contact_person_phone,
                'company_account_active_state' => (bool) $user->company_account_active_state,
            ];
        }

        // Firma musí mať aktívny účet
        if (in_array('company', $roles) && !$user->company_account_active_state) {
            return response()->json([
                'message' => 'Váš firemný účet ešte nebol aktivovaný. Skontrolujte svoj email a potvrďte registráciu.'
            ], 403);
        }

        //  kontrola, či používateľ musí zmeniť heslo
        if ($user->must_change_password) {
            return response()->json([
                'status' => 'FORCE_PASSWORD_CHANGE',
                'message' => 'Musíte zmeniť svoje heslo pred pokračovaním.',
                'user' => array_merge($baseUserPayload, $companyPayload),
            ], 200);
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
            'user' => array_merge($baseUserPayload, $companyPayload),
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
    // Logovanie požiadavky prichádzajúcej na server
    Log::info('Zmena hesla - prichádzajúci request:', [
        'email' => $request->email,
        'token' => $request->bearerToken(),
    ]);

    $request->validate([
        'current_password' => 'required|string',
        'new_password' => 'required|string|min:8|confirmed', // musí byť "new_password_confirmation" v requeste
        'email' => 'nullable|email' //  doplnené pre prípad, že používateľ nie je prihlásený
    ]);

    // Získame aktuálneho používateľa z tokenu
    $user = $request->user();

    //  Ak route nemá middleware auth:api, pokúsime sa načítať používateľa manuálne z tokenu
    if (!$user && $request->bearerToken()) {
        try {
            $user = auth('api')->user();
            Log::info('Používateľ načítaný manuálne z tokenu.', ['user_id' => optional($user)->id]);
        } catch (\Exception $e) {
            Log::warning('Nepodarilo sa načítať používateľa z tokenu.', ['chyba' => $e->getMessage()]);
        }
    }

    // Ak používateľ nie je prihlásený (napr. pri vynútenej zmene hesla)
    // skúsime ho nájsť podľa emailu, ktorý príde z frontendu
    if (!$user && $request->email) {
        $user = User::where('email', $request->email)->first();
        Log::info('Používateľ bol nájdený podľa emailu.', ['email' => $request->email]);
    }

    // Ak používateľ stále neexistuje, vrátime chybu
    if (!$user) {
        Log::error('Používateľ nebol nájdený.', ['email' => $request->email]);
        return response()->json([
            'message' => 'Používateľ nebol nájdený.'
        ], 404);
    }

    //  Ak používateľ nie je prihlásený a nemá flag must_change_password → zákaz
    if (!$request->bearerToken() && !$user->must_change_password) {
        Log::warning('Neautorizovaný prístup – bez tokenu a bez must_change_password.', ['email' => $request->email]);
        return response()->json([
            'message' => 'Neautorizovaný prístup.'
        ], 403);
    }

    // Overíme, či aktuálne heslo sedí
    if (!Hash::check($request->current_password, $user->password)) {
        Log::warning('Neplatné aktuálne heslo.', ['user_id' => $user->id]);
        return response()->json([
            'message' => 'Neplatné aktuálne heslo.'
        ], 403);
    }

    // Zmena hesla a reset flagu po úspešnej zmene
    $user->password = Hash::make($request->new_password);
    $user->must_change_password = false; // 🔹 reset flagu po úspešnej zmene
    $user->save();

    Log::info('Heslo bolo úspešne zmenené.', ['user_id' => $user->id]);

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

        /**
     * Zmena údajov používateľa (napr. alternatívny email)
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'alternative_email' => 'nullable|email',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Používateľ nie je prihlásený.'
            ], 401);
        }

        // Aktualizácia údajov
        $user->alternative_email = $request->alternative_email;
        $user->save();

        return response()->json([
            'message' => 'Údaje boli úspešne aktualizované.',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'alternative_email' => $user->alternative_email,
            ],
        ]);
    }
}
