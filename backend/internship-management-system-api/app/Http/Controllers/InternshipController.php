<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Illuminate\Http\Request;

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
                'id' => $internship->id,
                'year' => $internship->year,
                'semester' => $internship->semester,
                'start_date' => $internship->start_date,
                'end_date' => $internship->end_date,
                'status' => $internship->status,
                'company_id' => $internship->company_id,
                'company_name' => $internship->company ? ($internship->company->company_name ?? $internship->company->name ?? 'Neznáma firma') : 'Neznáma firma',
                'student_id' => $internship->student_id,
                'garant_id' => $internship->garant_id,
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
            'end_date' => 'required|date',
            'semester' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'year' => 'required|integer',
            'company_id' => 'required|exists:users,id',
            'student_id' => 'required|exists:users,id',
            'garant_id' => 'required|exists:users,id',
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
            $internship = Internship::create([
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'semester' => isset($validated['semester']) ? ucfirst($validated['semester']) : null,
                'status' => $validated['status'] ?? null,
                'year' => $validated['year'] ?? null,
                'company_id' => $validated['company_id'] ?? null,
                'student_id' => $validated['student_id'] ?? null,
                'garant_id' => $validated['garant_id'] ?? null,
            ]);

            return response()->json([
                'message' => 'Prax bola úspešne vytvorená.',
                'internship' => $internship->load(['company', 'student', 'garant']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Nepodarilo sa vytvoriť prax.',
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
            'end_date' => 'required|date',
        ]);

        // aktualizujeme len začiatok a koniec praxe
        $internship->update([
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        return response()->json([
            'message' => 'Prax bola úspešne aktualizovaná.',
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
            'id' => $internship->id,
            'year' => $internship->year,
            'semester' => $internship->semester,
            'start_date' => $internship->start_date,
            'end_date' => $internship->end_date,
            'status' => $internship->status,
            'company_id' => $internship->company_id,
            'company_name' => $internship->company ? ($internship->company->company_name ?? $internship->company->name ?? 'Neznáma firma') : 'Neznáma firma',
            'student_id' => $internship->student_id,
            'garant_id' => $internship->garant_id,
        ];
    });

    return response()->json($data);
}


}
