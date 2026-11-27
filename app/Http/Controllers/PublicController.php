<?php

namespace App\Http\Controllers;

use App\Support\PerPage;
use App\Support\PermohonanAudience;
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
        $validated = $request->validate([
            'nama_pemohon' => 'required|string|max:255',
            'email_pemohon' => 'nullable|email|max:255',
            'kontak_pemohon' => 'required|string|max:255',
            'pimpinan_pemohon' => 'nullable|string|max:255',
            'id_unit' => 'required|exists:unit,id_unit',
            // Optional, and must belong to the chosen unit.
            'id_sub_unit' => ['nullable', 'integer', Rule::exists('sub_unit', 'id_sub_unit')->where('id_unit', $request->input('id_unit'))],
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
            ->orderBy('id_permohonan', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json([
            'permohonans' => $permohonans,
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
