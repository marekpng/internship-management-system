<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
class RegisterController extends Controller
{
    public function registerStudent(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'student_email' => 'required|email|unique:users,student_email',
            'city' => 'nullable|string',
            'street' => 'nullable|string',
            'house_number' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'alternative_email' => 'nullable|email',
            'phone' => 'nullable|string',
            'study_field' => 'nullable|string',
        ]);

        $password = User::generateRandomPassword();

        $student = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->student_email,
            'student_email' => $request->student_email,
            'city' => $request->city,
            'street' => $request->street,
            'house_number' => $request->house_number,
            'postal_code' => $request->postal_code,
            'alternative_email' => $request->alternative_email,
            'phone' => $request->phone,
            'study_field' => $request->study_field,
            'password' => Hash::make($password),
            'must_change_password' => true,
        ]);

        // Priradenie roly
        $studentRole = Role::where('name', 'student')->first();
        if ($studentRole) {
            $student->roles()->attach($studentRole->id);
        }

        Mail::raw("Vaše prihlasovacie heslo je: $password\n\nPo prvom prihlásení si ho musíte zmeniť.", function ($message) use ($student) {
            $message->to($student->student_email)
                ->subject('Registrácia študenta - prihlasovacie údaje');
        });

        return response()->json([
            'message' => 'Študent bol úspešne zaregistrovaný. Heslo bolo odoslané emailom.',
        ]);
    }

    public function registerCompany(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string',
            'company_address' => 'nullable|string',
            'contact_person_name' => 'required|string',
            'contact_person_email' => 'required|email|unique:users,email',
            'contact_person_phone' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
        ]);


        $company = User::create([
            'company_name' => $request->company_name,
            'company_address' => $request->company_address,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_email' => $request->contact_person_email,
            'email' => $request->contact_person_email,
            'contact_person_phone' => $request->contact_person_phone,
            'password' => Hash::make($request->password),
            'must_change_password' => true, //todo mjaros opytat sa ze ci to ma byt true alebo false
            'company_account_active_state' => false,
        ]);

        $companyRole = Role::where('name', 'company')->first();
        if ($companyRole) {
            $company->roles()->attach($companyRole->id);
        }

        $activationUrl = URL::temporarySignedRoute(
            'company.activate',
            Carbon::now()->addMinutes(60),
            ['id' => $company->id]
        );

        Mail::raw("Vaša firma bola zaregistrovaná. Kliknite na nasledujúci odkaz pre aktiváciu účtu:\n\n$activationUrl", function ($message) use ($company) {
            $message->to($company->contact_person_email)
                ->subject('Aktivácia firemného účtu');
        });

        return response()->json([
            'message' => 'Firma bola úspešne zaregistrovaná. Aktivačný link bol odoslaný emailom.',
        ]);
    }

    public function activateCompany(Request $request, $id)
    {
        if (!$request->hasValidSignature()) {
            return response()->json(['error' => 'Aktivačný link je neplatný alebo expiroval.'], 403);
        }

        $company = User::findOrFail($id);

        if ($company->company_account_active_state) {
            return response()->json(['message' => 'Účet je už aktivovaný.']);
        }

        $company->update(['company_account_active_state' => true]);

        return response()->json(['message' => 'Účet bol úspešne aktivovaný.']);
    }

}
