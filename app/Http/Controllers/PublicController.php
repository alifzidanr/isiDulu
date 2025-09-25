<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\Unit;

class PublicController extends Controller
{
    public function index()
    {
        $permohonans = Permohonan::with(['unit'])
            ->orderBy('tanggal', 'desc')
            ->paginate(10);
            
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

        Permohonan::create($validated);

        return redirect()->route('public.index')->with('success', 'Permohonan berhasil disubmit!');
    }
}