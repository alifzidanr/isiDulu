<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewPermohonanNotification;

class PublicController extends Controller
{
    public function index()
    {
        $permohonans = Permohonan::with(['unit'])
            ->orderBy('tanggal', 'desc')
            ->paginate(25);
            
        return view('public.permohonan-public', compact('permohonans'));
    }

    public function showForm()
    {
        $units = Unit::with('kampus')->get();
        return view('public.form-permohonan', compact('units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pemohon' => 'required|string|max:255',
            'email_pemohon' => 'nullable|email|max:255',
            'kontak_pemohon' => 'required|string|max:255',
            'pimpinan_pemohon' => 'nullable|string|max:255',
            'id_unit' => 'required|exists:unit,id_unit',
            'inventaris' => 'required|in:y,n',
            'keluhan' => 'required|string',
        ]);

        $validated['tanggal'] = now();
        $validated['id_user'] = 1; // Default PIC
        $validated['status_permohonan'] = 0; // Permohonan

        $permohonan = Permohonan::create($validated);

        // Send Telegram notifications
        $this->sendTelegramNotification($permohonan);

        return redirect()->route('public.index')->with('success', 'Permohonan berhasil disubmit!');
    }

    /**
     * Get latest permohonan for real-time updates
     */
    public function getLatest(Request $request)
    {
        $lastId = $request->query('last_id', 0);
        
        $permohonans = Permohonan::with(['unit'])
            ->where('id_permohonan', '>', $lastId)
            ->orderBy('tanggal', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json([
            'permohonans' => $permohonans,
            'latest_id' => $permohonans->first()?->id_permohonan ?? $lastId,
            'count' => $permohonans->count()
        ]);
    }

    /**
     * Send Telegram notification for new permohonan from public form
     */
    private function sendTelegramNotification(Permohonan $permohonan)
    {
        try {
            $notification = new NewPermohonanNotification($permohonan);
            
            // 1. Send to group
            $this->sendToGroup($notification);
            
            // 2. Send to all responsible users
            $this->sendToResponsibleUsers($permohonan, $notification);
            
        } catch (\Exception $e) {
            \Log::error('Telegram notification failed', [
                'permohonan_id' => $permohonan->id_permohonan,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notification to Telegram group
     */
    private function sendToGroup($notification)
    {
        try {
            $groupChatId = config('services.telegram-bot-api.group_chat_id');
            
            if ($groupChatId) {
                Notification::route('telegram', $groupChatId)
                    ->notify($notification);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send to Telegram group', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notification to all users in charge of the kampus/unit
     */
    private function sendToResponsibleUsers(Permohonan $permohonan, $notification)
    {
        try {
            // Load unit with kampus relationship
            $permohonan->load('unit.kampus');
            
            $unitId = $permohonan->id_unit;
            $kampusId = $permohonan->unit->id_kampus ?? null;
            
            // Find all users responsible for this kampus or unit
            $responsibleUsers = User::query()
                ->where('status', 1)
                ->whereNotNull('telegram_id')
                ->where('telegram_id', '!=', '')
                ->where(function ($query) use ($kampusId, $unitId) {
                    $query->where('id_unit', $unitId)
                          ->orWhere('id_kampus', $kampusId);
                })
                ->get();

            // Send to each responsible user
            foreach ($responsibleUsers as $user) {
                try {
                    Notification::route('telegram', $user->telegram_id)
                        ->notify($notification);
                    
                    \Log::info('Sent notification to user', [
                        'user_id' => $user->id_user,
                        'user_name' => $user->nama_lengkap,
                        'telegram_id' => $user->telegram_id,
                        'permohonan_id' => $permohonan->id_permohonan
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send to user', [
                        'user_id' => $user->id_user,
                        'telegram_id' => $user->telegram_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to get responsible users', [
                'permohonan_id' => $permohonan->id_permohonan,
                'error' => $e->getMessage()
            ]);
        }
    }
}
