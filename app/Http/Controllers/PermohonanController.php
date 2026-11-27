<?php

namespace App\Http\Controllers;

use App\Support\PerPage;
use App\Support\PermohonanAudience;
use App\Support\PermohonanInput;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        // Users see what's assigned to their kampus, unit or sub unit;
        // read-only accounts (nothing assigned) see all.
        if ($user->isUser()) {
            PermohonanAudience::visibleTo($query, $user);
        }

        $permohonans = $query->orderBy('tanggal', 'desc')->paginate(PerPage::get())->withQueryString();
        
        $units = Unit::with(['kampus', 'subUnits'])->get();
        $jenisPerangkats = JenisPerangkat::orderBy('nama_perangkat')->get();
        $jenisPerawatans = JenisPerawatan::orderBy('nama_perawatan')->get();
        $detailPerawatans = DetailPerawatan::orderBy('nama_detail_perawatan')->get();

        $readOnly = $user->isReadOnly();

        return view('dashboard.permohonan', compact(
            'readOnly',
            'permohonans', 
            'units', 
            'jenisPerangkats', 
            'jenisPerawatans', 
            'detailPerawatans'
        ));
    }

    public function store(Request $request)
    {
        $this->abortIfReadOnly();

        $validated = PermohonanInput::validate($request);

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
        $this->abortIfReadOnly();

        $request->validate([
            'status' => 'required|integer|in:1,2,4,5'
        ]);

        $permohonan = $this->findVisible($id);
        
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
        $this->abortIfReadOnly();

        $permohonan = $this->findVisible($id);
        
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
        $permohonan = $this->findVisible($id, ['laporan.jenisPerangkat', 'laporan.jenisPerawatan',
                                               'laporan.detailPerawatan', 'laporan.creator', 'unit', 'subUnit']);

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

        // Users see what's assigned to their kampus, unit or sub unit;
        // read-only accounts (nothing assigned) see all.
        if ($user->isUser()) {
            PermohonanAudience::visibleTo($query, $user);
        }

        $permohonans = $query->orderBy('tanggal', 'desc')->paginate(PerPage::get())->withQueryString();

        return view('dashboard.print-permohonan', compact('permohonans'));
    }

    public function printSingle($id)
    {
        $permohonan = $this->findVisible($id, ['unit.kampus', 'subUnit', 'laporan.creator']);
        return view('dashboard.print-single', compact('permohonan'));
    }

    /**
     * Users may only open or act on permohonan assigned to their kampus, unit
     * or sub unit (the same rule as the list); others get 404.
     */
    private function findVisible($id, array $with = []): Permohonan
    {
        $query = Permohonan::with($with)->whereKey($id);
        $user = Auth::user();

        if ($user?->isUser()) {
            PermohonanAudience::visibleTo($query, $user);
        }

        return $query->firstOrFail();
    }

    private function abortIfReadOnly(): void
    {
        abort_if(Auth::user()?->isReadOnly(), 403, 'Akun Anda belum punya kampus/unit, jadi hanya bisa melihat permohonan.');
    }

    /**
     * Send Telegram notification for new permohonan
     * Sends to the group and every user assigned to its kampus, unit or sub unit (PermohonanAudience)
     */
    private function sendNewPermohonanNotification(Permohonan $permohonan)
    {
        try {
            PermohonanAudience::notify($permohonan, new NewPermohonanNotification($permohonan));
        } catch (\Exception $e) {
            \Log::error('Telegram new permohonan notification failed', [
                'permohonan_id' => $permohonan->id_permohonan,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send status change notification
     * Sends to the group and every user assigned to its kampus, unit or sub unit (PermohonanAudience)
     */
    private function sendStatusChangeNotification(Permohonan $permohonan, $oldStatus, $newStatus)
    {
        try {
            PermohonanAudience::notify($permohonan, new PermohonanStatusChangedNotification($permohonan, $oldStatus, $newStatus));
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
     * Sends to the group and every user assigned to its kampus, unit or sub unit (PermohonanAudience)
     */
    private function sendLaporanNotification(Laporan $laporan)
    {
        try {
            $laporan->load('permohonan');
            PermohonanAudience::notify($laporan->permohonan, new LaporanCreatedNotification($laporan));
        } catch (\Exception $e) {
            \Log::error('Telegram laporan notification failed', [
                'laporan_id' => $laporan->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
