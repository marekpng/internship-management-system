<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function registerStudent(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'student_email' => 'required|email|unique:users,student_email',
            'alternative_email' => 'nullable|email',
            'phone' => 'required|string|max:20',
            'study_field' => 'required|string|max:255',
        ]);

        // Vygenerujeme náhodné heslo
        $plainPassword = Str::random(10);

        // Vytvorenie používateľa
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'address' => $validated['address'],
            'student_email' => $validated['student_email'],
            'alternative_email' => $validated['alternative_email'] ?? null,
            'phone' => $validated['phone'],
            'study_field' => $validated['study_field'],
            'email' => $validated['student_email'], // používame email pre login
            'password' => Hash::make($plainPassword),
            'role' => 'student',
            'must_change_password' => true,
        ]);

        // Odoslanie emailu s heslom
        Mail::raw(
            "Dobrý deň {$user->first_name},\n\nVáš účet bol úspešne vytvorený.\nVaše dočasné heslo: {$plainPassword}\n\nPri prvom prihlásení si ho prosím zmeňte.",
            function ($message) use ($user) {
                $message->to($user->student_email)
                    ->subject('Registrácia - Pracovná prax');
            }
        );

        return response()->json([
            'message' => 'Študent bol úspešne zaregistrovaný. Heslo bolo odoslané na študentský email.',
            'user' => $user,
        ], 201);
    }
}
