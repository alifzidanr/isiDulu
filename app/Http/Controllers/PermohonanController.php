<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\Laporan;
use App\Models\Unit;
use App\Models\User;
use App\Models\JenisPerangkat;
use App\Models\JenisPerawatan;
use App\Models\DetailPerawatan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewPermohonanNotification;
use App\Notifications\PermohonanStatusChangedNotification;
use App\Notifications\LaporanCreatedNotification;

class PermohonanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Permohonan::with(['unit', 'subUnit', 'laporan']);

        if ($user->isUser()) {
            $unitIds = Unit::where('id_kampus', $user->id_kampus)->pluck('id_unit');
            $query->whereIn('id_unit', $unitIds);
        }

        $permohonans = $query->orderBy('tanggal', 'desc')->paginate(15);
        
        $units = Unit::with('kampus')->get();
        $jenisPerangkats = JenisPerangkat::orderBy('nama_perangkat')->get();
        $jenisPerawatans = JenisPerawatan::orderBy('nama_perawatan')->get();
        $detailPerawatans = DetailPerawatan::orderBy('nama_detail_perawatan')->get();

        return view('dashboard.permohonan', compact(
            'permohonans', 
            'units', 
            'jenisPerangkats', 
            'jenisPerawatans', 
            'detailPerawatans'
        ));
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
        $validated['id_user'] = auth()->id();
        $validated['status_permohonan'] = 0;

        $permohonan = Permohonan::create($validated);

        // Send Telegram notifications
        $this->sendNewPermohonanNotification($permohonan);

        return redirect()->route('permohonan.index')->with('success', 'Permohonan berhasil dibuat!');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:1,2,4,5'
        ]);

        $permohonan = Permohonan::findOrFail($id);
        
        // Capture old status BEFORE updating
        $oldStatus = $permohonan->status_permohonan;
        $newStatus = $request->status;
        
        // Update status
        $permohonan->status_permohonan = $newStatus;
        $permohonan->save();

        // Send notification with correct old and new status
        $this->sendStatusChangeNotification($permohonan, $oldStatus, $newStatus);

        return back()->with('success', 'Status permohonan berhasil diupdate!');
    }

    public function storeLaporan(Request $request, $id)
    {
        $permohonan = Permohonan::findOrFail($id);
        
        if ($permohonan->status_permohonan != 2) {
            return back()->with('error', 'Laporan hanya bisa dibuat untuk permohonan yang sudah selesai!');
        }

        if ($permohonan->laporan) {
            return back()->with('error', 'Laporan sudah dibuat untuk permohonan ini!');
        }

        $validated = $request->validate([
            'id_jenis_perangkat' => 'required|exists:jenis_perangkat,id_jenis_perangkat',
            'id_perawatan' => 'required|exists:jenis_perawatan,id_perawatan',
            'id_detail_perawatan' => 'required|exists:detail_perawatan,id_detail_perawatan',
            'detail_perangkat' => 'required|string|max:255',
            'uraian_pekerjaan' => 'required|string',
            'catatan' => 'nullable|string',
        ]);

        $validated['id_permohonan'] = $id;
        $validated['created_by'] = auth()->id();

        $laporan = Laporan::create($validated);

        // Send laporan created notification
        $this->sendLaporanNotification($laporan);

        return redirect()->route('permohonan.index')->with('success', 'Laporan berhasil dibuat!');
    }

    public function showLaporan($id)
    {
        $permohonan = Permohonan::with(['laporan.jenisPerangkat', 'laporan.jenisPerawatan', 
                                        'laporan.detailPerawatan', 'laporan.creator', 
                                        'unit', 'subUnit'])
                                ->findOrFail($id);

        if (!$permohonan->laporan) {
            return back()->with('error', 'Laporan tidak ditemukan!');
        }

        return view('dashboard.laporan-detail', compact('permohonan'));
    }

    public function print()
    {
        $user = Auth::user();
        $query = Permohonan::with(['unit', 'subUnit'])
            ->where('status_permohonan', 4);

        if ($user->isUser()) {
            $unitIds = Unit::where('id_kampus', $user->id_kampus)->pluck('id_unit');
            $query->whereIn('id_unit', $unitIds);
        }

        $permohonans = $query->orderBy('tanggal', 'desc')->paginate(15);

        return view('dashboard.print-permohonan', compact('permohonans'));
    }

    public function printSingle($id)
    {
        $permohonan = Permohonan::with(['unit', 'subUnit'])->findOrFail($id);
        return view('dashboard.print-single', compact('permohonan'));
    }

    /**
     * Send Telegram notification for new permohonan
     * Sends to: 1) Group, 2) All responsible users (kampus/unit match)
     */
    private function sendNewPermohonanNotification(Permohonan $permohonan)
    {
        try {
            $notification = new NewPermohonanNotification($permohonan);
            
            // 1. Send to group
            $this->sendToGroup($notification);
            
            // 2. Send to all responsible users
            $this->sendToResponsibleUsers($permohonan, $notification);
            
        } catch (\Exception $e) {
            \Log::error('Telegram new permohonan notification failed', [
                'permohonan_id' => $permohonan->id_permohonan,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send status change notification
     * Sends to: 1) Group, 2) All responsible users (kampus/unit match)
     */
    private function sendStatusChangeNotification(Permohonan $permohonan, $oldStatus, $newStatus)
    {
        try {
            $notification = new PermohonanStatusChangedNotification($permohonan, $oldStatus, $newStatus);
            
            // 1. Send to group
            $this->sendToGroup($notification);
            
            // 2. Send to all responsible users
            $this->sendToResponsibleUsers($permohonan, $notification);
            
        } catch (\Exception $e) {
            \Log::error('Telegram status notification failed', [
                'permohonan_id' => $permohonan->id_permohonan,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send laporan created notification
     * Sends to: 1) Group, 2) All responsible users (kampus/unit match)
     */
    private function sendLaporanNotification(Laporan $laporan)
    {
        try {
            $notification = new LaporanCreatedNotification($laporan);
            
            // 1. Send to group
            $this->sendToGroup($notification);
            
            // 2. Send to all responsible users
            $laporan->load('permohonan');
            $this->sendToResponsibleUsers($laporan->permohonan, $notification);
            
        } catch (\Exception $e) {
            \Log::error('Telegram laporan notification failed', [
                'laporan_id' => $laporan->id,
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
                ->where('status', 1) // Active users only
                ->whereNotNull('telegram_id')
                ->where('telegram_id', '!=', '')
                ->where(function ($query) use ($kampusId, $unitId) {
                    // Users assigned to this specific unit
                    $query->where('id_unit', $unitId)
                          // OR users assigned to this kampus (campus-wide responsibility)
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
