<?php
namespace App\Http\Controllers;

use App\Models\Internship;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        // Voliteľné: notifikácia do systému o zmene profilu
        Notification::create([
            'user_id' => $user->id,
            'type' => 'profile_change',
            'message' => 'Údaje profilu garanta boli aktualizované.',
        ]);

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
}
