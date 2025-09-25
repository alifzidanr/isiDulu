<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerangkatTerdaftar extends Model
{
    use HasFactory;

    protected $table = 'perangkat_terdaftar';
    protected $primaryKey = 'id_perangkat_terdaftar';
    protected $fillable = [
        'id_jenis_perangkat',
        'nama_perangkat_terdaftar',
        'pengguna',
        'inventaris'
    ];

    public function jenisPerangkat()
    {
        return $this->belongsTo(JenisPerangkat::class, 'id_jenis_perangkat');
    }
}