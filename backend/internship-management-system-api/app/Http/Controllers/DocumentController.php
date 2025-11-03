<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Získať všetky dokumenty.
     */
    public function index()
    {
        $documents = Document::all();
        return response()->json($documents);
    }

    /**
     * Získať detail dokumentu.
     */
    public function show($id)
    {
        $document = Document::findOrFail($id);
        return response()->json($document);
    }

    /**
     * Vytvoriť nový dokument.
     */
    public function store(Request $request)
    {
        $request->validate([
            'document_name' => 'required|string|max:255',
            'internship_id' => 'required|exists:internships,id',
        ]);

        $document = Document::create($request->all());
        return response()->json($document, 201);
    }

    /**
     * Aktualizovať dokument.
     */
    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        $request->validate([
            'document_name' => 'required|string|max:255',
            'internship_id' => 'required|exists:internships,id',
        ]);

        $document->update($request->all());
        return response()->json($document);
    }

    /**
     * Zmazať dokument.
     */
    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        $document->delete();
        return response()->json(null, 204);
    }
}
