<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Notification;
use App\Models\Internship;
use App\Models\User;

class CompanyController extends Controller
{
    /**
     * Vytvorí notifikácie pri zmene stavu praxe vykonanej firmou.
     *
     * Pravidlá (podľa zadania):
     * - Firma POTVRDÍ prax (stav: Potvrdená)  -> notifikovať študenta aj garanta.
     * - Firma ZAMIETNE prax (stav: Zamietnutá) -> notifikovať iba študenta.
     * - Pri inom stave (napr. Vytvorená) notifikáciu nevytvárame.
     *
     * Poznámka: kontrolujeme aj pôvodný stav, aby nevznikali duplicitné notifikácie,
     * keď frontend pošle rovnaký stav opakovane.
     */
    private function notifyAfterCompanyStatusChange(Internship $internship, string $oldStatus, string $newStatus): void
    {
        // Ak sa stav reálne nezmenil, nič nerobíme.
        if ($oldStatus === $newStatus) {
            return;
        }

        $company = $internship->company()->first();
        $companyName = $company?->company_name ?? 'firma';

        // 1) Potvrdená -> študent + garant
        if ($newStatus === 'Potvrdená') {
            // Študent (rešpektujeme jeho nastavenie notify_approved, ak existuje)
            $student = User::find($internship->student_id);
            if ($student && (bool) ($student->notify_approved ?? true)) {
                Notification::create([
                    'user_id'  => $internship->student_id,
                    'type'     => 'internship_status',
                    'message'  => 'Firma ' . $companyName . ' potvrdila vašu prax (stav: Potvrdená).',
                    'read'     => false,
                ]);
            }

            $garantId = (int) ($internship->garant_id ?? 0);
            if ($garantId > 0 && $garantId !== (int) $internship->student_id) {
                $garant = User::find($garantId);

                if ($garant && (bool) ($garant->notify_new_request ?? true)) {
                    Notification::create([
                        'user_id' => $garantId,
                        'type'    => 'internship_status',
                        'message' => 'Firma ' . $companyName . ' potvrdila prax študenta. Prax čaká na vaše schválenie.',
                        'read'    => false,
                    ]);

                    if (!empty($garant->email)) {
                        Mail::raw(
                            'Firma ' . $companyName . ' potvrdila prax študenta. Prax čaká na vaše schválenie.',
                            function ($message) use ($garant) {
                                $message->to($garant->email)
                                        ->subject('Nová prax na schválenie');
                            }
                        );
                    }
                }
            }

            return;
        }

        // 2) Zamietnutá -> iba študent
        if ($newStatus === 'Zamietnutá') {
            $student = User::find($internship->student_id);
            if ($student && (bool) ($student->notify_rejected ?? true)) {
                Notification::create([
                    'user_id' => $internship->student_id,
                    'type'    => 'internship_status',
                    'message' => 'Firma ' . $companyName . ' zamietla vašu prax (stav: Zamietnutá).',
                    'read'    => false,
                ]);
            }

            return;
        }

        // Iné stavy (napr. Vytvorená) -> bez notifikácie
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $companyId = $user->id;

        // Načítanie štatistík
        $pending = Internship::where('company_id', $companyId)
            ->where('status', 'Vytvorená')
            ->count();

        $approved = Internship::where('company_id', $companyId)
            ->whereIn('status', ['Potvrdená', 'Schválená', 'Obhájená', 'Neobhájená'])
            ->count();

        $rejected = Internship::where('company_id', $companyId)
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

        $internship = Internship::where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $oldStatus = $internship->status;
        $newStatus = $request->status;

        // Ak sa nič nemení, vrátime odpoveď bez vytvárania notifikácií.
        if ($oldStatus === $newStatus) {
            return response()->json([
                'message' => 'Stav ostal nezmenený.',
                'internship' => $internship
            ]);
        }

        $internship->status = $newStatus;
        $internship->save();

        // Notifikácie podľa pravidiel zo zadania (Potvrdená -> študent + garant, Zamietnutá -> študent)
        $this->notifyAfterCompanyStatusChange($internship, $oldStatus, $newStatus);

        return response()->json([
            'message' => 'Stav bol úspešne aktualizovaný.',
            'internship' => $internship
        ]);
    }

    public function filteredInternships(Request $request)
    {
        $companyId = $request->user()->id;
        $status = $request->query('status');

        return Internship::where('company_id', $companyId)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with(['student'])
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function list()
    {
        // Vráti všetkých používateľov, ktorí majú company_name vyplnené
        $companies = User::whereNotNull('company_name')
            ->select('id', 'company_name', 'email')
            ->get();

        return response()->json($companies);
    }

    public function pendingInternships(Request $request)
    {
        $companyId = $request->user()->id;

        $internships = Internship::where('company_id', $companyId)
            ->where('status', 'Vytvorená')
            ->with(['student'])
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json($internships);
    }

    public function createdInternships(Request $request)
    {
        return Internship::where('company_id', $request->user()->id)
            ->where('status', 'Vytvorená')
            ->with(['student'])
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function approvedInternships(Request $request)
    {
        return Internship::where('company_id', $request->user()->id)
            ->whereIn('status', ['Potvrdená', 'Schválená', 'Obhájená', 'Neobhájená'])
            ->with(['student'])
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function rejectedInternships(Request $request)
    {
        return Internship::where('company_id', $request->user()->id)
            ->where('status', 'Zamietnutá')
            ->with(['student'])
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * História praxí firmy.
     *
     * Pre firmy potrebujeme vidieť aj praxe, ktoré už nie sú len Vytvorená/Potvrdená/Zamietnutá
     * (napr. po rozhodnutí garanta alebo po obhajobe).
     *
     * Voliteľné filtre cez query parametre:
     * - status: presný status (string)
     * - student_id: id študenta (int)
     * - year: rok podľa created_at (int)
     * - semester: "zimny" | "letny" (podľa mesiaca created_at)
     */
    public function historyInternships(Request $request)
    {
        $companyId = $request->user()->id;

        $q = Internship::where('company_id', $companyId)
            ->with(['student'])
            ->orderBy('created_at', 'DESC');

        // Filter: status
        if ($request->filled('status')) {
            $q->where('status', $request->query('status'));
        }

        // Filter: student
        if ($request->filled('student_id')) {
            $q->where('student_id', (int) $request->query('student_id'));
        }

        // Filter: year (použi stĺpec `year`, ktorý už máš v DB)
        if ($request->filled('year')) {
            $q->where('year', (int) $request->query('year'));
        }

        // Filter: semester
        // Používame priamo stĺpec `semester` z DB (máš tam hodnoty ako: Zimný/Letný alebo Winter/Summer).
        // Dôvod: v DB už semester existuje a nemusí korelovať iba s mesiacom start_date.
        if ($request->filled('semester')) {
            $semesterRaw = trim((string) $request->query('semester'));
            $semester = mb_strtolower($semesterRaw);

            $winterAliases = ['zimny', 'zimný', 'winter', 'zima'];
            $summerAliases = ['letny', 'Letný', 'summer', 'leto'];

            if (in_array($semester, $winterAliases, true)) {
                $q->whereIn('semester', ['Zimný', 'Winter', 'zimný', 'winter']);
            } elseif (in_array($semester, $summerAliases, true)) {
                $q->whereIn('semester', ['Letný', 'Summer', 'letný', 'summer']);
            } else {
                // Ak príde priamo hodnota z DB (napr. "Letný"), skúsime ju filtrovať priamo.
                $q->where('semester', $semesterRaw);
            }
        }

        return response()->json($q->get());
    }

    public function internshipDetail($id, Request $request)
    {
        $companyId = $request->user()->id;

        $internship = Internship::where('id', $id)
            ->where('company_id', $companyId)
            ->with(['student', 'company'])
            ->firstOrFail();

        return response()->json($internship);
    }

    public function approveInternship($id, Request $request)
    {
        $internship = Internship::where('id', $id)->firstOrFail();

        $oldStatus = $internship->status;
        $internship->status = 'Potvrdená';
        $internship->save();

        // Notifikácie podľa pravidiel zo zadania
        $this->notifyAfterCompanyStatusChange($internship, $oldStatus, 'Potvrdená');

        $company = $internship->company()->first();

        // Email študentovi iba ak to má povolené v nastaveniach (notify_approved)
        if ($internship->student && !empty($internship->student->email) && (bool) ($internship->student->notify_approved ?? true)) {
            Mail::raw(
                'Vaša prax bola schválená firmou.',
                function ($message) use ($internship) {
                    $message->to($internship->student->email)
                            ->subject('Prax schválená');
                }
            );
        }

        return response()->json(['message' => 'Prax bola potvrdená.']);
    }

    public function rejectInternship($id, Request $request)
    {
        $internship = Internship::where('id', $id)->firstOrFail();

        $oldStatus = $internship->status;
        $internship->status = 'Zamietnutá';
        $internship->save();

        // Notifikácie podľa pravidiel zo zadania
        $this->notifyAfterCompanyStatusChange($internship, $oldStatus, 'Zamietnutá');

        $company = $internship->company()->first();

        // Email študentovi iba ak to má povolené v nastaveniach (notify_rejected)
        if ($internship->student && !empty($internship->student->email) && (bool) ($internship->student->notify_rejected ?? true)) {
            Mail::raw(
                'Vaša prax bola zamietnutá firmou.',
                function ($message) use ($internship) {
                    $message->to($internship->student->email)
                           ->subject('Prax zamietnutá');
                }
            );
        }

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
