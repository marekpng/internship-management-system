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
        $internships = Internship::all();
        return response()->json($internships);
    }

    /**
     * Získať detail stáže.
     */
    public function show($id)
    {
        $internship = Internship::findOrFail($id);
        return response()->json($internship);
    }

    /**
     * Vytvoriť novú stáž.
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'status' => 'required|string|max:255',
            'year' => 'required|integer',
            'company_id' => 'required|exists:companies,id',
            'student_id' => 'required|exists:users,id',
            'garant_id' => 'required|exists:users,id',
        ]);

        $internship = Internship::create($request->all());
        return response()->json($internship, 201);
    }

    /**
     * Aktualizovať stáž.
     */
    public function update(Request $request, $id)
    {
        $internship = Internship::findOrFail($id);

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'status' => 'required|string|max:255',
            'year' => 'required|integer',
            'company_id' => 'required|exists:companies,id',
            'student_id' => 'required|exists:users,id',
            'garant_id' => 'required|exists:users,id',
        ]);

        $internship->update($request->all());
        return response()->json($internship);
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
}
