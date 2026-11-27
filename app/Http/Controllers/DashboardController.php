<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\User;
use App\Models\Unit;
use App\Models\Kampus;
use App\Support\PermohonanAudience;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Statistics based on user access level
        $stats = [];
        
        // Superadmins and read-only accounts (no kampus/unit) see everything.
        $seesAll = $user->isSuperAdmin() || $user->hasNoPlacement();

        if ($seesAll) {
            $stats = [
                'total_permohonan' => Permohonan::count(),
                'permohonan_pending' => Permohonan::where('status_permohonan', 0)->count(),
                'permohonan_dikerjakan' => Permohonan::where('status_permohonan', 1)->count(),
                'permohonan_selesai' => Permohonan::where('status_permohonan', 2)->count(),
                'total_users' => User::count(),
                'total_kampus' => Kampus::count(),
                'total_units' => Unit::count(),
            ];
        } else {
            // Everyone else: what's assigned to their kampus, unit or sub unit.
            $mine = fn () => PermohonanAudience::visibleTo(Permohonan::query(), $user);

            $stats = [
                'total_permohonan' => $mine()->count(),
                'permohonan_pending' => $mine()->where('status_permohonan', 0)->count(),
                'permohonan_dikerjakan' => $mine()->where('status_permohonan', 1)->count(),
                'permohonan_selesai' => $mine()->where('status_permohonan', 2)->count(),
            ];
        }

        // Chart data for status distribution
        $statusData = Permohonan::select('status_permohonan', DB::raw('count(*) as count'))
            ->when(! $seesAll, fn ($query) => PermohonanAudience::visibleTo($query, $user))
            ->groupBy('status_permohonan')
            ->get();

        $recent = Permohonan::with('unit')
            ->when(! $seesAll, fn ($query) => PermohonanAudience::visibleTo($query, $user))
            ->orderByDesc('id_permohonan')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'statusData', 'recent'));
    }
}