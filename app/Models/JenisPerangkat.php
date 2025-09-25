<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisPerangkat extends Model
{
    use HasFactory;

    protected $table = 'jenis_perangkat';
    protected $primaryKey = 'id_jenis_perangkat';
    protected $fillable = ['nama_perangkat'];

    public function perangkatTerdaftars()
    {
        return $this->hasMany(PerangkatTerdaftar::class, 'id_jenis_perangkat');
    }
}