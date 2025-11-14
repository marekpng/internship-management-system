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
            'company' => [
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

    public function pendingInternships(Request $request)
    {
        $companyId = $request->user()->id;

        $internships = \App\Models\Internship::where('company_id', $companyId)
            ->where('status', 'Vytvorená')
            ->with(['student'])
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json($internships);
    }

    public function internshipDetail($id, Request $request)
    {
        $companyId = $request->user()->id;

        $internship = \App\Models\Internship::where('id', $id)
            ->where('company_id', $companyId)
            ->with(['student', 'company'])
            ->firstOrFail();

        return response()->json($internship);
    }

    public function approveInternship($id, Request $request)
    {
        $internship = \App\Models\Internship::where('id', $id)->firstOrFail();
        $internship->status = 'Potvrdená';
        $internship->save();

        return response()->json(['message' => 'Prax bola potvrdená.']);
    }

    public function rejectInternship($id, Request $request)
    {
        $internship = \App\Models\Internship::where('id', $id)->firstOrFail();
        $internship->status = 'Zamietnutá';
        $internship->save();

        return response()->json(['message' => 'Prax bola zamietnutá.']);
    }
}
