<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class CompanyController
{
    public function dashboard(Request $request)
{
    $user = $request->user();
    $companyId = $user->id;

    // Načítanie štatistík
    $pending = \App\Models\Internship::where('company_id', $companyId)
        ->where('status', 'Vytvorená')
        ->count();

    $approved = \App\Models\Internship::where('company_id', $companyId)
        ->where('status', 'Potvrdená')
        ->count();

    $rejected = \App\Models\Internship::where('company_id', $companyId)
        ->where('status', 'Zamietnutá')
        ->count();

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
        'stats' => [
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected
        ]
    ]);
}

    public function updateStatus($id, Request $request)
    {
        $companyId = $request->user()->id;

        $request->validate([
            'status' => 'required|in:Vytvorená,Potvrdená,Zamietnutá'
        ]);

        $internship = \App\Models\Internship::where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $internship->status = $request->status;
        $internship->save();

        return response()->json([
            'message' => 'Stav bol úspešne aktualizovaný.',
            'internship' => $internship
        ]);
    }

    public function filteredInternships(Request $request)
    {
        $companyId = $request->user()->id;
        $status = $request->query('status');

        return \App\Models\Internship::where('company_id', $companyId)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with(['student'])
            ->orderBy('created_at', 'DESC')
            ->get();
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

    public function createdInternships(Request $request)
    {
        return \App\Models\Internship::where('company_id', $request->user()->id)
            ->where('status', 'Vytvorená')
            ->with(['student'])
            ->orderBy('created_at', 'DESC')
            ->get();
    }

public function approvedInternships(Request $request)
{
    return \App\Models\Internship::where('company_id', $request->user()->id)
        ->where('status', 'Potvrdená')
        ->with(['student'])
        ->orderBy('created_at', 'DESC')
        ->get();
}

public function rejectedInternships(Request $request)
{
    return \App\Models\Internship::where('company_id', $request->user()->id)
        ->where('status', 'Zamietnutá')
        ->with(['student'])
        ->orderBy('created_at', 'DESC')
        ->get();
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

    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'company_name' => $user->company_name,
            'phone' => $user->phone,
            'street' => $user->street,
            'house_number' => $user->house_number,
            'city' => $user->city,
            'postal_code' => $user->postal_code,
            'contact_person_name' => $user->contact_person_name,
            'contact_person_email' => $user->contact_person_email,
            'contact_person_phone' => $user->contact_person_phone,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:255'],
            'contact_person_name' => ['nullable', 'string', 'max:255'],
            'contact_person_email' => ['nullable', 'email'],
            'contact_person_phone' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil bol aktualizovaný.',
            'company' => $user->fresh(),
        ]);
    }
}
