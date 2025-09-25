<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;

class PermohonanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Permohonan::with(['unit', 'subUnit']);

        // Filter based on user access level
        if ($user->isUser()) {
            $unitIds = Unit::where('id_kampus', $user->id_kampus)->pluck('id_unit');
            $query->whereIn('id_unit', $unitIds);
        }

        $permohonans = $query->orderBy('tanggal', 'desc')->paginate(15);
        
        // Get units for the create form
        $units = Unit::with('kampus')->get();

        return view('dashboard.permohonan', compact('permohonans', 'units'));
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
        $validated['id_user'] = auth()->id(); // Current user as PIC
        $validated['status_permohonan'] = 0; // Permohonan

        Permohonan::create($validated);

        return redirect()->route('permohonan.index')->with('success', 'Permohonan berhasil dibuat!');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:1,2,4,5' // Dikerjakan, Selesai, Disahkan, Dibatalkan
        ]);

        $permohonan = Permohonan::findOrFail($id);
        $permohonan->status_permohonan = $request->status;
        $permohonan->save();

        return back()->with('success', 'Status permohonan berhasil diupdate!');
    }

    public function print()
    {
        $user = Auth::user();
        $query = Permohonan::with(['unit', 'subUnit'])
            ->where('status_permohonan', 4); // Only Disahkan

        // Filter based on user access level
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