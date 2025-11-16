<?php
namespace App\Http\Controllers;

use App\Models\Internship;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InternshipController extends Controller
{
    /**
     * Získať všetky stáže.
     */
    public function index()
    {
        $internships = Internship::with(['company', 'student', 'garant'])->get();

        $data = $internships->map(function ($internship) {
            return [
                'id'           => $internship->id,
                'year'         => $internship->year,
                'semester'     => $internship->semester,
                'start_date'   => $internship->start_date,
                'end_date'     => $internship->end_date,
                'status'       => $internship->status,
                'company_id'   => $internship->company_id,
                'company_name' => $internship->company ? ($internship->company->company_name ?? $internship->company->name ?? 'Neznáma firma') : 'Neznáma firma',
                'student_id'   => $internship->student_id,
                'garant_id'    => $internship->garant_id,
            ];
        });

        return response()->json($data);
    }

    /**
     * Získať detail stáže.
     */
    public function show($id)
    {
        $internship = Internship::with(['company', 'student', 'garant'])->findOrFail($id);
        return response()->json($internship);
    }

    /**
     * Pridať novú stáž.
     */
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

        // Kontrola duplicity praxe pre rovnaký rok a semester
        if (
            Internship::where('student_id', $validated['student_id'])
            ->where('year', $validated['year'])
            ->where('semester', $validated['semester'])
            ->exists()
        ) {
            return response()->json(['error' => 'Študent už má vytvorenú prax pre tento rok a semester.'], 400);
        }

        try {
            // Pred vytvorením stáže priradíme garanta
            $garantId = $this->assignGarantToInternship();

            // Vytvorenie novej stáže s priradeným garantom
            $internship = Internship::create([
                'start_date' => $validated['start_date'] ?? null,
                'end_date'   => $validated['end_date'] ?? null,
                'semester'   => isset($validated['semester']) ? ucfirst($validated['semester']) : null,
                'status'     => $validated['status'] ?? null,
                'year'       => $validated['year'] ?? null,
                'company_id' => $validated['company_id'] ?? null,
                'student_id' => $validated['student_id'] ?? null,
                'garant_id'  => $garantId, // Garant sa priradí priamo sem
            ]);

            // Vytvorenie priečinka a generovanie PDF dohody
            Storage::disk('public')->makeDirectory('internships/agreements');

            try {
                $student = $internship->student;
                $company = $internship->company;
                $garant  = $internship->garant ?? (object) ['first_name' => 'N/A', 'last_name' => ''];

                $pdf = Pdf::loadView('pdf.agreement', [
                    'internship' => $internship,
                    'student'    => $student,
                    'company'    => $company,
                    'garant'     => $garant,
                ]);

                $pdfPath = "internships/agreements/internship_{$internship->id}.pdf";
                Storage::disk('public')->put($pdfPath, $pdf->output());

                // Uloženie cesty k PDF
                $internship->update(['agreement_pdf_path' => $pdfPath]);

            } catch (\Exception $e) {
                \Log::error('Chyba pri generovaní PDF dohody: ' . $e->getMessage());
            }

            return response()->json([
                'message'    => 'Prax bola úspešne vytvorená a PDF dohoda bola vygenerovaná.',
                'internship' => $internship->load(['company', 'student', 'garant']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Nepodarilo sa vytvoriť prax.',
                'details' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Aktualizovať stáž.
     */
    public function update(Request $request, $id)
    {
        $internship = Internship::findOrFail($id);

        // validujeme len polia, ktoré sa majú aktualizovať
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        // aktualizujeme len začiatok a koniec praxe
        $internship->update([
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
        ]);

        return response()->json([
            'message'    => 'Prax bola úspešne aktualizovaná.',
            'internship' => $internship->load(['company', 'student', 'garant']),
        ]);
    }

    /**
     * Zmazať stáž.
     */
    public function destroy($id)
    {
        $internship = Internship::findOrFail($id);
        $internship->delete();
        return response()->json(null, 204);
    }

    /**
     * Získať všetky praxe, kde dané token zodpovedá študentovi, firme alebo garantovi.
     */
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
                'company_name'       => $internship->company ? ($internship->company->company_name ?? $internship->company->name ?? 'Neznáma firma') : 'Neznáma firma',
                'student_id'         => $internship->student_id,
                'student_first_name' => $internship->student ? $internship->student->first_name : 'Nezadané meno',
                'student_last_name'  => $internship->student ? $internship->student->last_name : 'Nezadané priezvisko',
                'garant_id'          => $internship->garant_id,
                'garant_first_name'  => $internship->garant ? $internship->garant->first_name : 'Nezadané meno',
                'garant_last_name'   => $internship->garant ? $internship->garant->last_name : 'Nezadané priezvisko',
            ];
        });

        return response()->json($data);
    }

/**
 * Stiahnuť PDF dohodu pre konkrétnu prax.
 */
    public function downloadAgreement($id)
    {
        $internship = Internship::findOrFail($id);

        // Skontroluj, či má prax uloženú cestu k PDF
        $pdfPath = $internship->agreement_pdf_path ?? "internships/agreements/internship_{$id}.pdf";

        if (! \Storage::disk('public')->exists($pdfPath)) {
            return response()->json([
                'error' => 'PDF dohoda pre túto prax neexistuje.',
            ], 404);
        }

        // Stiahnutie PDF súboru
        return response()->download(
            storage_path('app/public/' . $pdfPath),
            "Dohoda_praxe_{$id}.pdf",
            ['Content-Type' => 'application/pdf']
        );
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
            'Obhájená',
            'Neobhájená',
        ];

        // Validácia požiadavky: stav musí byť jedným z povolených
        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', $validStatuses),
        ]);

        // Nájdeme stáž podľa ID
        $internship = Internship::findOrFail($id);

        // Aktualizujeme stav
        $internship->status = $validated['status'];
        $internship->save();

        return response()->json([
            'message'    => 'Stav stáže bol úspešne zmenený.',
            'internship' => $internship,
        ]);
    }

    private function assignGarantToInternship()
    {
        // Získaj všetkých garantov (užívateľov s roľou "garant")
        $garants = User::whereHas('roles', function ($query) {
            $query->where('name', 'garant');
        })->get();

        // Získaj počet priradených stáží pre každého garanta
        $garantCounts = [];
        foreach ($garants as $garant) {
            $garantCounts[$garant->id] = Internship::where('garant_id', $garant->id)->count();
        }

        // Nájdi garanta s najnižším počtom priradených stáží
        $minInternships   = min($garantCounts);
        $selectedGarantId = array_search($minInternships, $garantCounts);

        return $selectedGarantId;
    }

}
