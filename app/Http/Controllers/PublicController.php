<?php

namespace App\Http\Controllers;

use App\Support\PerPage;
use App\Support\PermohonanAudience;
use App\Support\PermohonanInput;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            ->orderBy('id_permohonan', 'desc')
            ->paginate(PerPage::get())->withQueryString();
            
        return view('public.permohonan-public', compact('permohonans'));
    }

    public function showForm()
    {
        $units = Unit::with(['kampus', 'subUnits'])->get();
        return view('public.form-permohonan', compact('units'));
    }

    public function store(Request $request)
    {
        $validated = PermohonanInput::validate($request, public: true);

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
            ->orderBy('id_permohonan', 'desc')
            ->limit(10)
            ->get();
        
        // Public endpoint: only what the public list shows; never contact
        // details, email, leader's name or who handles it.
        return response()->json([
            'permohonans' => $permohonans->map(fn (Permohonan $p) => [
                'id_permohonan' => $p->id_permohonan,
                'nama_pemohon' => $p->nama_pemohon,
                'unit' => ['nama_unit' => $p->unit?->nama_unit],
                'tanggal' => $p->tanggal,
                'keluhan' => \Illuminate\Support\Str::limit((string) $p->keluhan, 100),
                'status_permohonan' => $p->status_permohonan,
            ])->values(),
            'latest_id' => $permohonans->max('id_permohonan') ?? $lastId,
            'count' => $permohonans->count()
        ]);
    }

    /**
     * Send Telegram notification for new permohonan from public form
     */
    private function sendTelegramNotification(Permohonan $permohonan)
    {
        // The group and every user assigned to its kampus, unit or sub unit.
        PermohonanAudience::notify($permohonan, new NewPermohonanNotification($permohonan));
    }
}
