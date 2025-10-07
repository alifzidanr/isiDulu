<?php
// app/Models/Laporan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;

    protected $table = 'laporan';
    protected $primaryKey = 'id_laporan';
    
    protected $fillable = [
        'id_permohonan',
        'id_jenis_perangkat',
        'id_perawatan',
        'id_detail_perawatan',
        'detail_perangkat',
        'uraian_pekerjaan',
        'catatan',
        'created_by',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan');
    }

    public function jenisPerangkat()
    {
        return $this->belongsTo(JenisPerangkat::class, 'id_jenis_perangkat');
    }

    public function jenisPerawatan()
    {
        return $this->belongsTo(JenisPerawatan::class, 'id_perawatan');
    }

    public function detailPerawatan()
    {
        return $this->belongsTo(DetailPerawatan::class, 'id_detail_perawatan');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}