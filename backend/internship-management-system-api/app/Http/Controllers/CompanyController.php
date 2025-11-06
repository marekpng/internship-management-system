<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class CompanyController
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'message' => 'Vitaj, firma! Prístup k dashboardu je povolený.',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'company_name' => $user->company_name,
                'contact_person_name' => $user->contact_person_name,
                'role' => $user->role,
            ],
        ]);
    }
    public function list()
{
    // Vráti všetkých používateľov, ktorí majú company_name vyplnené
    $companies = \App\Models\User::whereNotNull('company_name')
        ->select('id', 'company_name', 'email')
        ->get();

    return response()->json($companies);
}
}
