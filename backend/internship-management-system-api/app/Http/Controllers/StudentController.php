<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Notification;
use App\Models\User;

class StudentController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'message' => 'Vitaj, študent! Prístup k dashboardu je povolený.',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Profil prihláseného študenta (zdroj pravdy = DB)
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
            'notify_profile_change' => (bool) $user->notify_profile_change,
        ]);
    }

    /**
     * Úprava profilu študenta (email nemeníme)
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:255'],
            'alternative_email' => ['nullable', 'email', 'max:255'],
        ]);

        $user->phone = $data['phone'] ?? null;
        $user->alternative_email = $data['alternative_email'] ?? null;
        $user->save();

        // Voliteľný email pri zmene profilu
        if ($user->notify_profile_change) {
            Mail::raw(
                'Vaše údaje profilu boli úspešne zmenené. Ak ste túto zmenu nevykonali vy, kontaktujte administrátora.',
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Zmena údajov profilu - Notifikácia');
                }
            );
        }

        Notification::create([
            'user_id' => $user->id,
            'type' => 'profile_change',
            'message' => 'Údaje profilu boli aktualizované.',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil bol aktualizovaný.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Načítanie notifikačných nastavení študenta
     */
    public function getNotifications(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'notify_profile_change' => (bool) $user->notify_profile_change,
        ]);
    }

    /**
     * Uloženie notifikačných nastavení študenta
     */
    public function updateNotifications(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'notify_profile_change' => ['required', 'boolean'],
        ]);

        // Študent môže ovládať iba email pri zmene profilu
        $user->notify_profile_change = (bool) $data['notify_profile_change'];

        // Ostatné emailové notifikácie pre študenta držíme vypnuté
        $user->notify_new_request = false;
        $user->notify_approved = false;
        $user->notify_rejected = false;

        $user->save();
    }

    /**
     * Zoznam notifikácií pre prihláseného študenta
     */
    public function getUserNotifications(Request $request)
    {
        return Notification::where('user_id', $request->user()->id)
            ->where('type', 'profile_change')
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
            ->where('type', 'profile_change')
            ->firstOrFail();

        $notification->read = true;
        $notification->save();

        return response()->json(['status' => 'success']);
    }
}
