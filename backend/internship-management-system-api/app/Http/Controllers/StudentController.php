<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Notification;

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

        // Email iba ak si to študent zapol (toto je jediné emailové nastavenie pre študenta)
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
            'read' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil bol aktualizovaný.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Načítanie notifikačných nastavení študenta (iba profile-change email)
     */
    public function getNotifications(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'notify_profile_change' => (bool) $user->notify_profile_change,
        ]);
    }

    /**
     * Uloženie notifikačných nastavení študenta (iba profile-change email)
     */
    public function updateNotifications(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'notify_profile_change' => ['required', 'boolean'],
        ]);

        $user->notify_profile_change = (bool) $data['notify_profile_change'];
        $user->save();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'notification_settings',
            'message' => 'Notifikačné nastavenia boli aktualizované.',
            'read' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikačné nastavenia boli uložené.',
        ]);
    }

    /**
     * Zoznam notifikácií pre prihláseného študenta
     * - zobrazujeme všetko (aj prax)
     * - odkliknúť (mark read) môže iba profile_change
     */
    public function getUserNotifications(Request $request)
    {
        // Študent má vidieť všetky notifikácie a musí ich vedieť aj odkliknúť (označiť ako prečítané)
        $items = Notification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        // Frontend používa tento flag na zobrazenie tlačidla ✔
        $items->transform(function ($n) {
            $n->can_mark_read = true;
            return $n;
        });

        return $items;
    }

    /**
     * Označenie notifikácie ako prečítanej (študent môže iba profile_change)
     */
    public function markNotificationRead($id, Request $request)
    {
        // Študent môže označiť ako prečítanú iba vlastnú notifikáciu
        $notification = Notification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->read = true;
        $notification->save();

        return response()->json(['status' => 'success']);
    }
}
