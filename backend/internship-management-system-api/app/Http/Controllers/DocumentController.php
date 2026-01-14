<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function uploadStudentDocument(Request $request, $internshipId)
    {
        $request->validate([
            'document_type' => 'required|in:report,signed_agreement',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $internship = Internship::findOrFail($internshipId);

        if ($request->user()->id !== $internship->student_id) {
            return response()->json(['message' => 'Nemáte oprávnenie nahrať dokument.'], 403);
        }

        if ($request->document_type === 'signed_agreement' && $internship->status !== 'Potvrdená') {
            return response()->json(['message' => 'Podpísanú zmluvu je možné nahrať až po potvrdení praxe.'], 400);
        }

        Storage::makeDirectory('public/internships/documents');

        $path = $request->file('file')->store('public/internships/documents');
        $relativePath = str_replace('public/', '', $path);

        $document = Document::create([
            'document_name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $relativePath,
            'type' => $request->document_type,
            'internship_id' => $internshipId,
            'uploaded_by' => $request->user()->id,
            'company_status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Dokument bol úspešne nahraný.',
            'document' => $document
        ], 201);
    }

    public function uploadGarantDocument(Request $request, $internshipId)
    {
        $request->validate([
            'document_type' => 'required|in:review',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $internship = Internship::findOrFail($internshipId);

        if (auth('api')->id() !== $internship->garant_id) {
            return response()->json(['message' => 'Nemáte oprávnenie nahrať dokument.'], 403);
        }

        Storage::makeDirectory('public/internships/documents');

        $path = $request->file('file')->store('public/internships/documents');
        $relativePath = str_replace('public/', '', $path);

        $document = Document::create([
            'document_name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $relativePath,
            'type' => $request->document_type,
            'internship_id' => $internshipId,
            'uploaded_by' => auth('api')->id(),
            'company_status' => 'submitted'
        ]);

        return response()->json([
            'message' => 'Dokument bol úspešne nahraný.',
            'document' => $document
        ], 201);
    }

    public function listByInternship($internshipId)
    {
        $documents = Document::where('internship_id', $internshipId)->get();
        return response()->json($documents);
    }

    /**
     * STIAHNUTIE DOKUMENTU
     */
    public function download($documentId)
    {
        if (!auth('api')->check()) {
            return response()->json(['message' => 'Neautorizovaný prístup.'], 401);
        }

        $document = Document::findOrFail($documentId);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return response()->json(['message' => 'Súbor neexistuje.'], 404);
        }

        $absolutePath = Storage::disk('public')->path($document->file_path);
        $mime = mime_content_type($absolutePath) ?: 'application/octet-stream';

        // Názov súboru (podľa DB)
        $fileName = $document->document_name ?: 'dokument';

        // RFC5987 pre diakritiku (filename*)
        $fileNameStar = rawurlencode($fileName);

        return response()->download(
            $absolutePath,
            $fileName,
            [
                'Content-Type' => $mime,
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"; filename*=UTF-8''{$fileNameStar}",
            ]
        );
    }

    public function approveDocumentByGarant($documentId)
    {
        if (!auth('api')->check()) {
            return response()->json(['message' => 'Neautorizovaný prístup'], 401);
        }

        $document = Document::findOrFail($documentId);
        $internship = Internship::findOrFail($document->internship_id);

        if (auth('api')->id() !== $internship->garant_id) {
            return response()->json(['message' => 'Nemáte oprávnenie schváliť tento dokument.'], 403);
        }

        $document->company_status = 'approved';
        $document->save();

        return response()->json(['message' => 'Dokument bol schválený garantom.']);
    }

    public function rejectDocumentByGarant($documentId)
    {
        if (!auth('api')->check()) {
            return response()->json(['message' => 'Neautorizovaný prístup'], 401);
        }

        $document = Document::findOrFail($documentId);
        $internship = Internship::findOrFail($document->internship_id);

        if (auth('api')->id() !== $internship->garant_id) {
            return response()->json(['message' => 'Nemáte oprávnenie zamietnuť tento dokument.'], 403);
        }

        $document->company_status = 'rejected';
        $document->save();

        return response()->json(['message' => 'Dokument bol zamietnutý garantom.']);
    }

    public function show($id)
    {
        return response()->json(Document::findOrFail($id));
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return response()->json(['message' => 'Dokument bol zmazaný.']);
    }
}
