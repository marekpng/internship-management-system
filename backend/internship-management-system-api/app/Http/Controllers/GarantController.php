<?php
namespace App\Http\Controllers;

use App\Models\Internship;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class GarantController extends Controller
{
    public function dashboard(Request $request)
    {
        return response()->json([
            'status'  => 'success',
            'message' => 'Vitaj, garant!',
            'user'    => $request->user(),
        ]);
    }

    /**
     * Filtrovanie podľa stavu
     */
    public function getByStatus(Request $request, $status = null)
    {
        $allowed = [
            'Vytvorená', 'Potvrdená', 'Schválená', 'Neschválená', 'Zamietnutá', 'Obhájená', 'Neobhájená',
        ];

        $garantId = $request->user()->id;

        $query = Internship::where('garant_id', $garantId)
            ->with(['student', 'company']);
        if ($status && in_array($status, $allowed)) {
            $query->where('status', $status);
        }
        return response()->json($query->orderBy('created_at', 'DESC')->get());
    }

    public function getCountByStatus($status)
    {
        $allowed = [
            'Vytvorená',
            'Potvrdená',
            'Schválená',
            'Neschválená',
            'Zamietnutá',
            'Obhájená',
            'Neobhájená',
        ];

        if (! in_array($status, $allowed)) {
            return response()->json(['error' => 'Neplatný stav'], 400);
        }

        $count = Internship::where('status', $status)->count();

        return response()->json([
            'status' => $status,
            'count'  => $count,
        ]);
    }

    /**
     * Detail praxe
     */
    public function internshipDetail($id)
    {
        return Internship::with(['student', 'company'])->findOrFail($id);
    }

    /**
     * Garant schvaľuje iba praxe v stave Potvrdená
     */
    public function approveInternship(Request $request, $id)
    {
        $garantId = $request->user()->id;

        $internship = Internship::findOrFail($id);

        $internship->status = 'Schválená';
        $internship->garant_id = $garantId; // ✅ kto zmenil stav
        $internship->save();

        return response()->json(['message' => 'Prax bola schválená garantom.']);
    }

    public function disapproveInternship(Request $request, $id)
    {
        $garantId = $request->user()->id;

        $internship = Internship::findOrFail($id);

        $internship->status = 'Neschválená';
        $internship->garant_id = $garantId; // ✅ kto zmenil stav
        $internship->save();

        return response()->json(['message' => 'Prax bola neschválená garantom.']);
    }

    private function sendEmailToStudent(?User $student, string $subject, string $body): void
    {
        if (!$student) return;

        $emails = [];
        if (!empty($student->email)) {
            $emails[] = $student->email;
        }
        if (!empty($student->alternative_email) && $student->alternative_email !== $student->email) {
            $emails[] = $student->alternative_email;
        }

        foreach (array_unique($emails) as $to) {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
        }
    }

    private function sendEmailToCompany(?User $company, string $toggleField, string $subject, string $body): void
    {
        if (!$company) return;

        $to = !empty($company->contact_person_email)
            ? $company->contact_person_email
            : $company->email;

        if (!empty($to) && (bool) ($company->{$toggleField} ?? true)) {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
        }
    }

    /**
     * Notifikácie a emaily po rozhodnutí garanta (obhájená / neobhájená prax)
     */
    private function notifyAfterGarantDefenseDecision(Internship $internship, string $newStatus): void
    {
        $garant = User::find($internship->garant_id);
        $garantName = trim(($garant?->first_name ?? '') . ' ' . ($garant?->last_name ?? ''));
        if ($garantName === '') $garantName = 'Garant';

        $student = User::find($internship->student_id);
        $studentName = trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? ''));
        if ($studentName === '') $studentName = 'Študent';

        $company = User::find($internship->company_id);
        $companyName = $company?->company_name ?? 'firma';

        // Texty notifikácií a emailov
        if ($newStatus === 'Obhájená') {
            $msgStudent = $garantName . ' označil(a) vašu prax ako obhájenú.';
            $msgCompany = $garantName . ' označil(a) prax študenta ' . $studentName . ' ako obhájenú.';
            $subjectStudent = 'Prax obhájená';
            $subjectCompany = 'Prax obhájená';
            $companyToggle = 'notify_approved';
        } else { // Neobhájená
            $msgStudent = $garantName . ' označil(a) vašu prax ako neobhájenú.';
            $msgCompany = $garantName . ' označil(a) prax študenta ' . $studentName . ' ako neobhájenú.';
            $subjectStudent = 'Prax neobhájená';
            $subjectCompany = 'Prax neobhájená';
            $companyToggle = 'notify_rejected';
        }

        // Študent: ZVONČEK vždy, EMAIL vždy (na primárny aj alternatívny, ak je)
        if ($student && (int) $student->id !== (int) $internship->garant_id) {
            Notification::create([
                'user_id' => $student->id,
                'type'    => 'internship_status',
                'message' => $msgStudent,
                'read'    => false,
            ]);

            $this->sendEmailToStudent($student, $subjectStudent, $msgStudent);
        }

        // Firma: ZVONČEK vždy, EMAIL iba podľa nastavenia
        if ($company && (int) $company->id !== (int) $internship->garant_id) {
            Notification::create([
                'user_id' => $company->id,
                'type'    => 'internship_status',
                'message' => $msgCompany,
                'read'    => false,
            ]);

            $this->sendEmailToCompany($company, $companyToggle, $subjectCompany, $msgCompany);
        }


    }

    /**
     * Notifikácie a emaily po schválení/neschválení praxe garantom
     */
    private function notifyAfterGarantApprovalDecision(Internship $internship, string $newStatus): void
    {
        $garant = User::find($internship->garant_id);
        $garantName = trim(($garant?->first_name ?? '') . ' ' . ($garant?->last_name ?? ''));
        if ($garantName === '') $garantName = 'Garant';

        $student = User::find($internship->student_id);
        $studentName = trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? ''));
        if ($studentName === '') $studentName = 'Študent';

        $company = User::find($internship->company_id);
        $companyName = $company?->company_name ?? 'firma';

        if ($newStatus === 'Schválená') {
            $msgStudent = $garantName . ' schválil(a) vašu prax.';
            $msgCompany = $garantName . ' schválil(a) prax študenta ' . $studentName . '.';
            $subjectStudent = 'Prax schválená garantom';
            $subjectCompany = 'Prax schválená garantom';
            $companyToggle = 'notify_approved';
        } else { // Neschválená
            $msgStudent = $garantName . ' neschválil(a) vašu prax.';
            $msgCompany = $garantName . ' neschválil(a) prax študenta ' . $studentName . '.';
            $subjectStudent = 'Prax neschválená garantom';
            $subjectCompany = 'Prax neschválená garantom';
            $companyToggle = 'notify_rejected';
        }

        // Študent: ZVONČEK vždy, EMAIL vždy (na primárny aj alternatívny, ak je)
        if ($student && (int) $student->id !== (int) $internship->garant_id) {
            Notification::create([
                'user_id' => $student->id,
                'type'    => 'internship_status',
                'message' => $msgStudent,
                'read'    => false,
            ]);

            $this->sendEmailToStudent($student, $subjectStudent, $msgStudent);
        }

        // Firma: ZVONČEK vždy, EMAIL iba podľa nastavenia
        if ($company && (int) $company->id !== (int) $internship->garant_id) {
            Notification::create([
                'user_id' => $company->id,
                'type'    => 'internship_status',
                'message' => $msgCompany,
                'read'    => false,
            ]);

            $this->sendEmailToCompany($company, $companyToggle, $subjectCompany, $msgCompany);
        }


    }

    /**
     * Garant obhajuje iba praxe v stave Schválená
     */
    public function markDefended(Request $request, $id)
    {
        $garantId = $request->user()->id;

        $internship = Internship::findOrFail($id);

        $internship->status = 'Obhájená';
        $internship->garant_id = $garantId; // ✅ kto zmenil stav
        $internship->save();

        return response()->json(['message' => 'Prax bola označená ako obhájená.']);
    }

    public function markNotDefended(Request $request, $id)
    {
        $garantId = $request->user()->id;

        $internship = Internship::findOrFail($id);

        $internship->status = 'Neobhájená';
        $internship->garant_id = $garantId; // ✅ kto zmenil stav
        $internship->save();

        return response()->json(['message' => 'Prax bola označená ako neobhájená.']);
    }

    /**
     * Profil prihláseného garanta
     * Zdroj pravdy je tabuľka users (garant je user s rolou garant)
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
            'alternative_email' => $user->alternative_email,

            // notifikačné nastavenia (aby to frontend vedel zobraziť aj bez ďalšieho callu)
            'notify_new_request' => (bool) $user->notify_new_request,
            'notify_approved' => (bool) $user->notify_approved,
            'notify_rejected' => (bool) $user->notify_rejected,
            'notify_profile_change' => (bool) $user->notify_profile_change,
        ]);
    }


    public function updateProfile(Request $request)
    {
        $user = $request->user();

        // Validujeme len to, čo garant reálne môže meniť
        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:255'],
            'alternative_email' => ['nullable', 'email', 'max:255'],
        ]);

        // Uloženie (pozor: ak používaš $fillable, musí obsahovať phone a alternative_email)
        $user->fill($data);
        $user->save();

        // ZVONČEK: vždy
        Notification::create([
            'user_id' => $user->id,
            'type' => 'profile_change',
            'message' => 'Údaje profilu garanta boli aktualizované.',
        ]);

        // EMAIL: iba podľa nastavenia
        if ((bool) ($user->notify_profile_change ?? false) && !empty($user->email)) {
            Mail::raw(
                'Údaje vášho profilu boli úspešne zmenené. Ak ste túto zmenu nevykonali vy, kontaktujte administrátora.',
                function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Zmena údajov profilu - Notifikácia');
                }
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profil bol aktualizovaný.',
            'user' => $user->only([
                'id',
                'email',
                'first_name',
                'last_name',
                'phone',
                'alternative_email',
                'notify_new_request',
                'notify_approved',
                'notify_rejected',
                'notify_profile_change',
            ]),
        ]);
    }

    /**
     * Načítanie notifikačných nastavení pre garanta
     * (checkboxy v settings)
     */
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

    /**
     * Uloženie notifikačných nastavení pre garanta
     */
    public function updateNotifications(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'notify_new_request' => ['required', 'boolean'],
            'notify_approved' => ['required', 'boolean'],
            'notify_rejected' => ['required', 'boolean'],
            'notify_profile_change' => ['required', 'boolean'],
        ]);

        $user->notify_new_request = $data['notify_new_request'];
        $user->notify_approved = $data['notify_approved'];
        $user->notify_rejected = $data['notify_rejected'];
        $user->notify_profile_change = $data['notify_profile_change'];
        $user->save();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'notification_settings',
            'message' => 'Notifikačné nastavenia garanta boli aktualizované.',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikačné nastavenia boli uložené.',
        ]);
    }

    /**
     * Zoznam notifikácií pre prihláseného garanta
     * Načítava iba notifikácie patriace aktuálnemu userovi
     */
    public function getUserNotifications(Request $request)
    {
        return Notification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Označenie notifikácie ako prečítanej (iba vlastnej)
     */
    public function markNotificationRead($id, Request $request)
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->read = true;
        $notification->save();

        return response()->json(['status' => 'success']);
    }

    public function listStudents(Request $request)
{
    // len istota - route už je v role:garant middleware
    $students = User::whereHas('roles', function ($q) {
            $q->where('name', 'student');
        })
        ->select('id', 'first_name', 'last_name', 'email', 'alternative_email', 'student_email')
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();

    return response()->json($students);
}
}
