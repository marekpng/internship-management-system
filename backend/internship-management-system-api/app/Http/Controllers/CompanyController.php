<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Notification;

class CompanyController extends Controller
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

        // Create notification for student
        Notification::create([
            'user_id' => $internship->student_id,
            'type' => 'status_update',
            'message' => 'Stav vašej praxe bol zmenený na: ' . $internship->status,
        ]);

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

        $company = $internship->company()->first();

        if ($internship->student && $internship->student->email && $company && $company->notify_approved) {
            Mail::raw(
                'Vaša prax bola schválená firmou.',
                function ($message) use ($internship) {
                    $message->to($internship->student->email)
                            ->subject('Prax schválená');
                }
            );
        }

        Notification::create([
            'user_id' => $internship->student_id,
            'type' => 'approved',
            'message' => 'Vaša prax bola schválená firmou ' . ($company->company_name ?? ''),
        ]);

        // Notify company itself
        Notification::create([
            'user_id' => $company->id,
            'type' => 'approved_company',
            'message' => 'Schválili ste prax študenta: ' . ($internship->student->name ?? ''),
        ]);

        return response()->json(['message' => 'Prax bola potvrdená.']);
    }

    public function rejectInternship($id, Request $request)
    {
        $internship = \App\Models\Internship::where('id', $id)->firstOrFail();
        $internship->status = 'Zamietnutá';
        $internship->save();

        $company = $internship->company()->first();

        if ($internship->student && $internship->student->email && $company && $company->notify_rejected) {
            Mail::raw(
                'Vaša prax bola zamietnutá firmou.',
                function ($message) use ($internship) {
                    $message->to($internship->student->email)
                           ->subject('Prax zamietnutá');
                }
            );
        }

        Notification::create([
            'user_id' => $internship->student_id,
            'type' => 'rejected',
            'message' => 'Vaša prax bola zamietnutá firmou ' . ($company->company_name ?? ''),
        ]);

        // Notify company
        Notification::create([
            'user_id' => $company->id,
            'type' => 'rejected_company',
            'message' => 'Zamietli ste prax študenta: ' . ($internship->student->name ?? ''),
        ]);

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
            'notify_new_request' => $user->notify_new_request,
            'notify_approved' => $user->notify_approved,
            'notify_rejected' => $user->notify_rejected,
            'notify_profile_change' => $user->notify_profile_change,
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
            'street' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($data);

        $user->street = $request->street;
        $user->house_number = $request->house_number;
        $user->city = $request->city;
        $user->postal_code = $request->postal_code;
        $user->save();

        if ($user->notify_profile_change) {
            Mail::raw(
                'Vaše firemné údaje boli úspešne zmenené. Ak ste túto zmenu nevykonali vy, kontaktujte administrátora.',
                function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Zmena firemných údajov - Notifikácia');
                }
            );
        }

        Notification::create([
            'user_id' => $user->id,
            'type' => 'profile_change',
            'message' => 'Úspešne ste aktualizovali firemné údaje.',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil bol aktualizovaný.',
            'company' => $user->fresh(),
        ]);
    }

    public function updateNotificationSettings(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'notify_new_request' => ['required', 'boolean'],
            'notify_approved' => ['required', 'boolean'],
            'notify_rejected' => ['required', 'boolean'],
            'notify_profile_change' => ['required', 'boolean'],
        ]);

        // Uloženie nastavení
        $user->notify_new_request = $data['notify_new_request'];
        $user->notify_approved = $data['notify_approved'];
        $user->notify_rejected = $data['notify_rejected'];
        $user->notify_profile_change = $data['notify_profile_change'];
        $user->save();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'notification_settings',
            'message' => 'Notifikačné nastavenia boli aktualizované.',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikačné nastavenia boli uložené.'
        ]);
    }

    public function getNotifications(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'notify_new_request' => (bool) $user->notify_new_request,
            'notify_approved' => (bool) $user->notify_approved,
            'notify_rejected' => (bool) $user->notify_rejected,
            'notify_profile_change' => (bool) $user->notify_profile_change,
        ]);
    }

    public function updateNotifications(Request $request)
    {
        // reuse existing logic so we have a single place for validation & saving
        return $this->updateNotificationSettings($request);
    }

    public function getUserNotifications(Request $request)
    {
        return Notification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function markNotificationRead($id, Request $request)
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->read = true;
        $notification->save();

        return response()->json(['status' => 'success']);
    }
}
