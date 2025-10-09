<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    // Registrácia študenta
    public function registerStudent(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'student_email' => 'required|email|unique:users,student_email',
            'address' => 'nullable|string',
            'alternative_email' => 'nullable|email',
            'phone' => 'nullable|string',
            'study_field' => 'nullable|string',
        ]);

        $password = User::generateRandomPassword();

        $student = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'student_email' => $request->student_email,
            'email' => $request->student_email, // hlavný login email
            'address' => $request->address,
            'alternative_email' => $request->alternative_email,
            'phone' => $request->phone,
            'study_field' => $request->study_field,
            'role' => 'Student',
            'password' => Hash::make($password),
            'must_change_password' => true,
        ]);

        // Odoslanie emailu
        Mail::raw("Vaše prihlasovacie heslo je: $password", function ($message) use ($student) {
            $message->to($student->student_email)
                ->subject('Registrácia študenta');
        });

        return response()->json([
            'message' => 'Študent bol úspešne zaregistrovaný. Heslo bolo odoslané emailom.',
        ]);
    }

    // Registrácia firmy
    public function registerCompany(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string',
            'company_address' => 'nullable|string',
            'contact_person_name' => 'required|string',
            'contact_person_email' => 'required|email|unique:users,email',
            'contact_person_phone' => 'nullable|string',
        ]);

        $password = User::generateRandomPassword();

        $company = User::create([
            'company_name' => $request->company_name,
            'company_address' => $request->company_address,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_email' => $request->contact_person_email,
            'email' => $request->contact_person_email,
            'contact_person_phone' => $request->contact_person_phone,
            'role' => 'Company',
            'password' => Hash::make($password),
            'must_change_password' => true,
        ]);

        // Odoslanie emailu
        Mail::raw("Vaše prihlasovacie heslo je: $password", function ($message) use ($company) {
            $message->to($company->contact_person_email)
                ->subject('Registrácia firmy');
        });

        return response()->json([
            'message' => 'Firma bola úspešne zaregistrovaná. Heslo bolo odoslané emailom.',
        ]);
    }
}
