<?php
// app/Http/Controllers/PermohonanController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\Laporan;
use App\Models\Unit;
use App\Models\JenisPerangkat;
use App\Models\JenisPerawatan;
use App\Models\DetailPerawatan;
use Illuminate\Support\Facades\Auth;

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

        Permohonan::create($validated);

        return redirect()->route('permohonan.index')->with('success', 'Permohonan berhasil dibuat!');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:1,2,4,5'
        ]);

        $permohonan = Permohonan::findOrFail($id);
        $permohonan->status_permohonan = $request->status;
        $permohonan->save();

        return back()->with('success', 'Status permohonan berhasil diupdate!');
    }

    public function storeLaporan(Request $request, $id)
    {
        $permohonan = Permohonan::findOrFail($id);
        
        // Check if user is the one who completed the request
        if ($permohonan->status_permohonan != 2) {
            return back()->with('error', 'Laporan hanya bisa dibuat untuk permohonan yang sudah selesai!');
        }

        // Check if laporan already exists
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

        Laporan::create($validated);

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
}