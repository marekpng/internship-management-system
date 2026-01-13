<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use App\Models\User;
use App\Models\Notification;
use App\Services\AgreementGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class InternshipController extends Controller
{
    public function index()
    {
        $internships = Internship::with(['company', 'student', 'garant'])->get();

        $data = $internships->map(function ($internship) {
            return [
                'id'                 => $internship->id,
                'year'               => $internship->year,
                'semester'           => $internship->semester,
                'start_date'         => $internship->start_date,
                'end_date'           => $internship->end_date,
                'status'             => $internship->status,
                'company_id'         => $internship->company_id,
                'company_name'       => $internship->company
                    ? ($internship->company->company_name ?? $internship->company->name ?? 'Neznáma firma')
                    : 'Neznáma firma',
                'student_id'         => $internship->student_id,
                'garant_id'          => $internship->garant_id,
                'agreement_pdf_path' => $internship->agreement_pdf_path,
            ];
        });

        return response()->json($data);
    }

    public function show($id)
    {
        $internship = Internship::with(['company', 'student', 'garant'])->findOrFail($id);
        return response()->json($internship);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
            'semester'   => 'required|string|max:255',
            'status'     => 'required|string|max:255',
            'year'       => 'required|integer',
            'company_id' => 'required|exists:users,id',
            'student_id' => 'required|exists:users,id',
        ]);

        if (
            Internship::where('student_id', $validated['student_id'])
                ->where('year', $validated['year'])
                ->where('semester', $validated['semester'])
                ->exists()
        ) {
            return response()->json(['error' => 'Študent už má vytvorenú prax pre tento rok a semester.'], 400);
        }

        try {
            $garantId = $this->assignGarantToInternship();

            $internship = Internship::create([
                'start_date' => $validated['start_date'] ?? null,
                'end_date'   => $validated['end_date'] ?? null,
                'semester'   => isset($validated['semester']) ? ucfirst($validated['semester']) : null,
                'status'     => $validated['status'] ?? null,
                'year'       => $validated['year'] ?? null,
                'company_id' => $validated['company_id'] ?? null,
                'student_id' => $validated['student_id'] ?? null,
                'garant_id'  => $garantId,
            ]);

            // --- NOTIFIKÁCIA PRE FIRMU: študent vytvoril prax ---
            try {
                $companyId = (int) ($internship->company_id ?? 0);
                $studentId = (int) ($internship->student_id ?? 0);

                if ($companyId > 0) {
                    $company = User::find($companyId);
                    $student = $studentId > 0 ? User::find($studentId) : null;

                    $studentName = trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? ''));
                    if ($studentName === '') {
                        $studentName = 'Študent';
                    }

                    $semester = $internship->semester ?? '-';
                    $year = $internship->year ?? '-';

                    $msg = $studentName . ' vytvoril(a) novú prax pre semester ' . $semester . ' a rok ' . $year . '. Čaká na vaše potvrdenie.';

                    // 🔔 zvonček notifikácia vždy (bez ohľadu na checkboxy)
                    Notification::create([
                        'user_id' => $companyId,
                        'type'    => 'internship_created',
                        'message' => $msg,
                        'read'    => false,
                    ]);

                    // ✉️ email iba ak má firma zapnuté notify_new_request
                    $emailEnabled = $company ? (bool) ($company->notify_new_request ?? true) : true;
                    $toEmail = $company?->contact_person_email ?: ($company?->email ?? null);

                    if ($emailEnabled && !empty($toEmail)) {
                        Mail::raw($msg, function ($message) use ($toEmail) {
                            $message->to($toEmail)
                                ->subject('Nová prax na potvrdenie');
                        });
                    }
                }
            } catch (\Throwable $e) {
                // notifikácia nesmie zablokovať vytvorenie praxe
                Log::warning('Failed to create company notification for internship ' . ($internship->id ?? 'unknown') . ': ' . $e->getMessage());
            }

            return response()->json([
                'message'    => 'Prax bola úspešne vytvorená.',
                'internship' => $internship->load(['company', 'student', 'garant']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Nepodarilo sa vytvoriť prax.',
                'details' => $e->getMessage(),
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $internship = Internship::findOrFail($id);

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        $internship->update([
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
        ]);

        return response()->json([
            'message'    => 'Prax bola úspešne aktualizovaná.',
            'internship' => $internship->load(['company', 'student', 'garant']),
        ]);
    }

    public function destroy($id)
    {
        $internship = Internship::findOrFail($id);
        $internship->delete();
        return response()->json(null, 204);
    }

    public function myInternships(Request $request)
    {
        $user = $request->user();

        $internships = Internship::with(['company', 'student', 'garant'])
            ->where('student_id', $user->id)
            ->orWhere('company_id', $user->id)
            ->orWhere('garant_id', $user->id)
            ->get();

        $data = $internships->map(function ($internship) {
            return [
                'id'                 => $internship->id,
                'year'               => $internship->year,
                'semester'           => $internship->semester,
                'start_date'         => $internship->start_date,
                'end_date'           => $internship->end_date,
                'status'             => $internship->status,
                'company_id'         => $internship->company_id,
                'company_name'       => $internship->company
                    ? ($internship->company->company_name ?? $internship->company->name ?? 'Neznáma firma')
                    : 'Neznáma firma',
                'student_id'         => $internship->student_id,
                'student_first_name' => $internship->student?->first_name ?? 'Nezadané meno',
                'student_last_name'  => $internship->student?->last_name ?? 'Nezadané priezvisko',
                'student_email'      => $internship->student?->email ?? null,
                'garant_id'          => $internship->garant_id,
                'garant_first_name'  => $internship->garant?->first_name ?? 'Nezadané meno',
                'garant_last_name'   => $internship->garant?->last_name ?? 'Nezadané priezvisko',
                'agreement_pdf_path' => $internship->agreement_pdf_path,
            ];
        });

        return response()->json($data);
    }

    public function myInternshipsNew(Request $request)
    {
        $user = $request->user();

        // Získame všetky stáže, kde je študent, firma alebo garant
        $internships = Internship::with(['company', 'student', 'garant'])
            ->where('student_id', $user->id)
            ->orWhere('company_id', $user->id)
            ->orWhere('garant_id', $user->id)
            ->get();

        // Mapujeme dáta na požiadavaný formát
        $data = $internships->map(function ($internship) {
            return [
                'id'                 => $internship->id,
                'year'               => $internship->year,
                'semester'           => $internship->semester,
                'created_at'         => $internship->created_at,
                'start_date'         => $internship->start_date,
                'end_date'           => $internship->end_date,
                'status'             => $internship->status,
                'company'            => $internship->company ? $internship->company->toArray() : null, // Všetky dáta o firme
                'student'            => $internship->student ? $internship->student->toArray() : null, // Všetky dáta o študentovi
                'garant_id'          => $internship->garant_id,
                'garant_first_name'  => $internship->garant?->first_name ?? 'Nezadané meno',
                'garant_last_name'   => $internship->garant?->last_name ?? 'Nezadané priezvisko',
                'agreement_pdf_path' => $internship->agreement_pdf_path,
            ];
        });

        return response()->json($data);
    }

    /**
     * Stiahnuť PDF dohodu pre konkrétnu prax.
     */
    public function downloadAgreement($id, AgreementGenerator $generator)
    {
        $internship = Internship::findOrFail($id);

        if (
            !$internship->agreement_pdf_path ||
            !Storage::disk('public')->exists($internship->agreement_pdf_path)
        ) {
            try {
                $path = $generator->generatePdf($internship);
                $internship->agreement_pdf_path = $path;
                $internship->save();
            } catch (\Throwable $e) {
                Log::error('PDF generation/download failed: ' . $e->getMessage());

                return response()->json([
                    'error' => 'Nepodarilo sa vygenerovať PDF dohodu.',
                    'details' => $e->getMessage(),
                ], 500);
            }
        }

        // absolútna cesta na disk
        $absPath = Storage::disk('public')->path($internship->agreement_pdf_path);

        // (len istota) refresh FS cache
        clearstatcache(true, $absPath);

        if (!is_file($absPath)) {
            return response()->json(['message' => 'Súbor neexistuje.'], 404);
        }

        $fileName = "Dohoda_praxe_{$internship->id}.pdf";

        $response = new BinaryFileResponse($absPath);
        $response->headers->set('Content-Type', 'application/pdf');

        // správny Content-Disposition (ASCII fallback + UTF-8 filename*)
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName,
            // fallback bez diakritiky (keby si niekedy dal do názvu diakritiku)
            "Dohoda_praxe_{$internship->id}.pdf"
        );

        // nech to browser necachuje (pri testovaní pomáha)
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    /**
     * Zmeniť stav stáže.
     */
    public function changeStatus($id, Request $request)
    {
        $validStatuses = [
            'Vytvorená',
            'Potvrdená',
            'Schválená',
            'Zamietnutá',
            'Neschválená',
            'Obhájená',
            'Neobhájená',
        ];

        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', $validStatuses),
        ]);

        $internship = Internship::findOrFail($id);
        $internship->status = $validated['status'];
        $internship->save();

        return response()->json([
            'message'    => 'Stav stáže bol úspešne zmenený.',
            'internship' => $internship,
        ]);
    }

    private function assignGarantToInternship()
    {
        $garants = User::whereHas('roles', function ($query) {
            $query->where('name', 'garant');
        })->get();

        if ($garants->isEmpty()) {
            throw new \RuntimeException('Neboli nájdení žiadni garanti.');
        }

        $garantCounts = [];
        foreach ($garants as $garant) {
            $garantCounts[$garant->id] = Internship::where('garant_id', $garant->id)->count();
        }

        $minInternships   = min($garantCounts);
        $selectedGarantId = array_search($minInternships, $garantCounts);

        return $selectedGarantId;
    }

    public function getApprovedInternships()
    {
        $internships = Internship::where('status', 'Schválená')
            ->with(['student', 'garant', 'company'])
            ->get();

        return response()->json([
            'count' => $internships->count(),
            'data'  => $internships,
        ]);
    }

    public function markAsDefended($id)
    {
        $internship = Internship::find($id);

        if (!$internship) {
            return response()->json(['message' => 'Praxe s daným ID neexistuje.'], 404);
        }

        if ($internship->status !== 'Schválená') {
            return response()->json([
                'message' => "Praxe nie je v stave 'Schválená', aktuálny stav je: {$internship->status}",
            ], 400);
        }

        $internship->status = 'Obhájená';
        $internship->save();

        return response()->json([
            'message'    => 'Stav praxe bol úspešne zmenený na Obhájená.',
            'internship' => $internship,
        ]);
    }

    public function getByStatus(Request $request, $status = null)
    {
        $allowed = [
            'Vytvorená', 'Potvrdená', 'Schválená', 'Neschválená', 'Zamietnutá', 'Obhájená', 'Neobhájená',
        ];

        $garantId = $request->user()->id;

        $query = Internship::where('garant_id', $garantId)
            ->with(['student', 'company']);

        if ($status && in_array($status, $allowed, true)) {
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

        if (!in_array($status, $allowed, true)) {
            return response()->json(['error' => 'Neplatný stav'], 400);
        }

        $count = Internship::where('status', $status)->count();

        return response()->json([
            'status' => $status,
            'count'  => $count,
        ]);
    }
}
