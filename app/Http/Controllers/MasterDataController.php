<?php
// app/Http/Controllers/MasterDataController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kampus;
use App\Models\Unit;
use App\Models\SubUnit;
use App\Models\User;
use App\Models\JenisPerangkat;
use App\Models\JenisPerawatan;
use App\Models\DetailPerawatan;
use App\Models\PerangkatTerdaftar;
use Illuminate\Support\Facades\Hash;

class MasterDataController extends Controller
{
    // KAMPUS METHODS
    public function kampusIndex()
    {
        $kampuses = Kampus::orderBy('nama_kampus')->paginate(10);
        return view('master.kampus.index', compact('kampuses'));
    }

    public function kampusStore(Request $request)
    {
        $request->validate([
            'nama_kampus' => 'required|string|max:255|unique:kampus,nama_kampus'
        ]);

        Kampus::create($request->only('nama_kampus'));
        return redirect()->route('master.kampus.index')->with('success', 'Kampus berhasil ditambahkan!');
    }

    public function kampusUpdate(Request $request, $id)
    {
        $request->validate([
            'nama_kampus' => 'required|string|max:255|unique:kampus,nama_kampus,' . $id . ',id_kampus'
        ]);

        $kampus = Kampus::findOrFail($id);
        $kampus->update($request->only('nama_kampus'));
        return redirect()->route('master.kampus.index')->with('success', 'Kampus berhasil diupdate!');
    }

    public function kampusDestroy($id)
    {
        $kampus = Kampus::findOrFail($id);
        $kampus->delete();
        return redirect()->route('master.kampus.index')->with('success', 'Kampus berhasil dihapus!');
    }

    // UNIT METHODS
    public function unitIndex()
    {
        $units = Unit::with('kampus')->orderBy('nama_unit')->paginate(10);
        $kampuses = Kampus::all();
        return view('master.unit.index', compact('units', 'kampuses'));
    }

    public function unitStore(Request $request)
    {
        $request->validate([
            'nama_unit' => 'required|string|max:255',
            'id_kampus' => 'required|exists:kampus,id_kampus'
        ]);

        Unit::create($request->only('nama_unit', 'id_kampus'));
        return redirect()->route('master.unit.index')->with('success', 'Unit berhasil ditambahkan!');
    }

    public function unitUpdate(Request $request, $id)
    {
        $request->validate([
            'nama_unit' => 'required|string|max:255',
            'id_kampus' => 'required|exists:kampus,id_kampus'
        ]);

        $unit = Unit::findOrFail($id);
        $unit->update($request->only('nama_unit', 'id_kampus'));
        return redirect()->route('master.unit.index')->with('success', 'Unit berhasil diupdate!');
    }

    public function unitDestroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();
        return redirect()->route('master.unit.index')->with('success', 'Unit berhasil dihapus!');
    }

    // SUB UNIT METHODS
    public function subUnitIndex()
    {
        $subUnits = SubUnit::with('unit.kampus')->orderBy('nama_sub_unit')->paginate(10);
        $units = Unit::with('kampus')->get();
        return view('master.sub-unit.index', compact('subUnits', 'units'));
    }

    public function subUnitStore(Request $request)
    {
        $request->validate([
            'nama_sub_unit' => 'required|string|max:255',
            'id_unit' => 'required|exists:unit,id_unit'
        ]);

        SubUnit::create($request->only('nama_sub_unit', 'id_unit'));
        return redirect()->route('master.sub-unit.index')->with('success', 'Sub Unit berhasil ditambahkan!');
    }

    public function subUnitUpdate(Request $request, $id)
    {
        $request->validate([
            'nama_sub_unit' => 'required|string|max:255',
            'id_unit' => 'required|exists:unit,id_unit'
        ]);

        $subUnit = SubUnit::findOrFail($id);
        $subUnit->update($request->only('nama_sub_unit', 'id_unit'));
        return redirect()->route('master.sub-unit.index')->with('success', 'Sub Unit berhasil diupdate!');
    }

    public function subUnitDestroy($id)
    {
        $subUnit = SubUnit::findOrFail($id);
        $subUnit->delete();
        return redirect()->route('master.sub-unit.index')->with('success', 'Sub Unit berhasil dihapus!');
    }

    // USER METHODS
    public function userIndex()
    {
        $users = User::with(['kampus', 'unit'])->orderBy('nama_lengkap')->paginate(10);
        $kampuses = Kampus::all();
        $units = Unit::all();
        return view('master.user.index', compact('users', 'kampuses', 'units'));
    }

    public function userStore(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:user,email',
            'telegram_id' => 'nullable|string|max:100|unique:user,telegram_id',
            'password' => 'required|string|min:6',
            'status' => 'required|in:active,inactive',
            'id_kampus' => 'nullable|exists:kampus,id_kampus',
            'id_unit' => 'nullable|exists:unit,id_unit',
            'access_level' => 'required|integer|in:0,1,2'
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($request->password);

        User::create($data);
        return redirect()->route('master.user.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function userUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:user,email,' . $id . ',id_user',
            'telegram_id' => 'nullable|string|max:100|unique:user,telegram_id,' . $id . ',id_user',
            'status' => 'required|in:active,inactive',
            'id_kampus' => 'nullable|exists:kampus,id_kampus',
            'id_unit' => 'nullable|exists:unit,id_unit',
            'access_level' => 'required|integer|in:0,1,2'
        ]);

        $data = $request->except('password');
        
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return redirect()->route('master.user.index')->with('success', 'User berhasil diupdate!');
    }

    // JENIS PERANGKAT METHODS
    public function jenisPerangkatIndex()
    {
        $jenisPerangkats = JenisPerangkat::orderBy('nama_perangkat')->paginate(10);
        return view('master.jenis-perangkat.index', compact('jenisPerangkats'));
    }

    public function jenisPerangkatStore(Request $request)
    {
        $request->validate([
            'nama_perangkat' => 'required|string|max:255'
        ]);

        JenisPerangkat::create($request->only('nama_perangkat'));
        return redirect()->route('master.jenis-perangkat.index')->with('success', 'Jenis Perangkat berhasil ditambahkan!');
    }

    public function jenisPerangkatUpdate(Request $request, $id)
    {
        $request->validate([
            'nama_perangkat' => 'required|string|max:255'
        ]);

        $jenisPerangkat = JenisPerangkat::findOrFail($id);
        $jenisPerangkat->update($request->only('nama_perangkat'));
        return redirect()->route('master.jenis-perangkat.index')->with('success', 'Jenis Perangkat berhasil diupdate!');
    }

    public function jenisPerangkatDestroy($id)
    {
        $jenisPerangkat = JenisPerangkat::findOrFail($id);
        $jenisPerangkat->delete();
        return redirect()->route('master.jenis-perangkat.index')->with('success', 'Jenis Perangkat berhasil dihapus!');
    }

    // JENIS PERAWATAN METHODS
    public function jenisPerawatanIndex()
    {
        $jenisPerawatans = JenisPerawatan::orderBy('nama_perawatan')->paginate(10);
        return view('master.jenis-perawatan.index', compact('jenisPerawatans'));
    }

    public function jenisPerawatanStore(Request $request)
    {
        $request->validate([
            'nama_perawatan' => 'required|string|max:255'
        ]);

        JenisPerawatan::create($request->only('nama_perawatan'));
        return redirect()->route('master.jenis-perawatan.index')->with('success', 'Jenis Perawatan berhasil ditambahkan!');
    }

    public function jenisPerawatanUpdate(Request $request, $id)
    {
        $request->validate([
            'nama_perawatan' => 'required|string|max:255'
        ]);

        $jenisPerawatan = JenisPerawatan::findOrFail($id);
        $jenisPerawatan->update($request->only('nama_perawatan'));
        return redirect()->route('master.jenis-perawatan.index')->with('success', 'Jenis Perawatan berhasil diupdate!');
    }

    public function jenisPerawatanDestroy($id)
    {
        $jenisPerawatan = JenisPerawatan::findOrFail($id);
        $jenisPerawatan->delete();
        return redirect()->route('master.jenis-perawatan.index')->with('success', 'Jenis Perawatan berhasil dihapus!');
    }

    // DETAIL PERAWATAN METHODS
    public function detailPerawatanIndex()
    {
        $detailPerawatans = DetailPerawatan::orderBy('nama_detail_perawatan')->paginate(10);
        return view('master.detail-perawatan.index', compact('detailPerawatans'));
    }

    public function detailPerawatanStore(Request $request)
    {
        $request->validate([
            'nama_detail_perawatan' => 'required|string|max:255'
        ]);

        DetailPerawatan::create($request->only('nama_detail_perawatan'));
        return redirect()->route('master.detail-perawatan.index')->with('success', 'Detail Perawatan berhasil ditambahkan!');
    }

    public function detailPerawatanUpdate(Request $request, $id)
    {
        $request->validate([
            'nama_detail_perawatan' => 'required|string|max:255'
        ]);

        $detailPerawatan = DetailPerawatan::findOrFail($id);
        $detailPerawatan->update($request->only('nama_detail_perawatan'));
        return redirect()->route('master.detail-perawatan.index')->with('success', 'Detail Perawatan berhasil diupdate!');
    }

    public function detailPerawatanDestroy($id)
    {
        $detailPerawatan = DetailPerawatan::findOrFail($id);
        $detailPerawatan->delete();
        return redirect()->route('master.detail-perawatan.index')->with('success', 'Detail Perawatan berhasil dihapus!');
    }

    // PERANGKAT TERDAFTAR METHODS
    public function perangkatTerdaftarIndex()
    {
        $perangkatTerdaftars = PerangkatTerdaftar::with('jenisPerangkat')->orderBy('nama_perangkat_terdaftar')->paginate(10);
        $jenisPerangkats = JenisPerangkat::all();
        return view('master.perangkat-terdaftar.index', compact('perangkatTerdaftars', 'jenisPerangkats'));
    }

    public function perangkatTerdaftarStore(Request $request)
    {
        $request->validate([
            'nama_perangkat_terdaftar' => 'required|string|max:255',
            'id_jenis_perangkat' => 'required|exists:jenis_perangkat,id_jenis_perangkat',
            'pengguna' => 'nullable|string|max:255',
            'inventaris' => 'required|in:y,n'
        ]);

        PerangkatTerdaftar::create($request->all());
        return redirect()->route('master.perangkat-terdaftar.index')->with('success', 'Perangkat Terdaftar berhasil ditambahkan!');
    }

    public function perangkatTerdaftarUpdate(Request $request, $id)
    {
        $request->validate([
            'nama_perangkat_terdaftar' => 'required|string|max:255',
            'id_jenis_perangkat' => 'required|exists:jenis_perangkat,id_jenis_perangkat',
            'pengguna' => 'nullable|string|max:255',
            'inventaris' => 'required|in:y,n'
        ]);

        $perangkatTerdaftar = PerangkatTerdaftar::findOrFail($id);
        $perangkatTerdaftar->update($request->all());
        return redirect()->route('master.perangkat-terdaftar.index')->with('success', 'Perangkat Terdaftar berhasil diupdate!');
    }

    public function perangkatTerdaftarDestroy($id)
    {
        $perangkatTerdaftar = PerangkatTerdaftar::findOrFail($id);
        $perangkatTerdaftar->delete();
        return redirect()->route('master.perangkat-terdaftar.index')->with('success', 'Perangkat Terdaftar berhasil dihapus!');
    }
}