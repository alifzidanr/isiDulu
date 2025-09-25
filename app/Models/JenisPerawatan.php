<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisPerawatan extends Model
{
    use HasFactory;

    protected $table = 'jenis_perawatan';
    protected $primaryKey = 'id_perawatan';
    protected $fillable = ['nama_perawatan'];
}