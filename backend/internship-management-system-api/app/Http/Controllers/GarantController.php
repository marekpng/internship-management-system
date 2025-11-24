<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Internship;

class GarantController extends Controller
{
    public function dashboard(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Vitaj, garant!',
            'user' => $request->user(),
        ]);
    }

    /**
     * Filtrovanie podľa stavu
     */
    public function getByStatus($status)
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

        if (!in_array($status, $allowed)) {
            return response()->json(['error' => 'Neplatný stav'], 400);
        }

        return Internship::where('status', $status)
            ->with(['student', 'company'])
            ->orderBy('created_at', 'DESC')
            ->get();
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
    public function approveInternship($id)
    {
        $internship = Internship::findOrFail($id);

        if ($internship->status !== 'Potvrdená') {
            return response()->json(['error' => 'Prax nie je v stave Potvrdená'], 400);
        }

        $internship->status = 'Schválená';
        $internship->save();

        return response()->json(['message' => 'Prax bola schválená garantom.']);
    }

    /**
     * Garant neschvaľuje iba praxe v stave Potvrdená
     */
    public function disapproveInternship($id)
    {
        $internship = Internship::findOrFail($id);

        if ($internship->status !== 'Potvrdená') {
            return response()->json(['error' => 'Prax nie je v stave Potvrdená'], 400);
        }

        $internship->status = 'Neschválená';
        $internship->save();

        return response()->json(['message' => 'Prax bola označená ako neschválená garantom.']);
    }

    /**
     * Garant obhajuje iba praxe v stave Schválená
     */
    public function markDefended($id)
    {
        $internship = Internship::findOrFail($id);

        if ($internship->status !== 'Schválená') {
            return response()->json(['error' => 'Prax nie je v stave Schválená'], 400);
        }

        $internship->status = 'Obhájená';
        $internship->save();

        return response()->json(['message' => 'Prax bola označená ako obhájená.']);
    }

    /**
     * Garant označuje neobhájené praxe
     */
    public function markNotDefended($id)
    {
        $internship = Internship::findOrFail($id);

        if ($internship->status !== 'Schválená') {
            return response()->json(['error' => 'Prax nie je v stave Schválená'], 400);
        }

        $internship->status = 'Neobhájená';
        $internship->save();

        return response()->json(['message' => 'Prax bola označená ako neobhájená.']);
    }
}
