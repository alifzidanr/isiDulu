<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\User;
use App\Models\Unit;
use App\Models\Kampus;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Statistics based on user access level
        $stats = [];
        
        if ($user->isSuperAdmin()) {
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
            // For regular users, show campus-specific stats
            $kampusId = $user->id_kampus;
            $unitIds = Unit::where('id_kampus', $kampusId)->pluck('id_unit');
            
            $stats = [
                'total_permohonan' => Permohonan::whereIn('id_unit', $unitIds)->count(),
                'permohonan_pending' => Permohonan::whereIn('id_unit', $unitIds)->where('status_permohonan', 0)->count(),
                'permohonan_dikerjakan' => Permohonan::whereIn('id_unit', $unitIds)->where('status_permohonan', 1)->count(),
                'permohonan_selesai' => Permohonan::whereIn('id_unit', $unitIds)->where('status_permohonan', 2)->count(),
            ];
        }

        // Chart data for status distribution
        $statusData = Permohonan::select('status_permohonan', DB::raw('count(*) as count'))
            ->when(!$user->isSuperAdmin(), function ($query) use ($user) {
                $unitIds = Unit::where('id_kampus', $user->id_kampus)->pluck('id_unit');
                return $query->whereIn('id_unit', $unitIds);
            })
            ->groupBy('status_permohonan')
            ->get();

        return view('dashboard.index', compact('stats', 'statusData'));
    }
}