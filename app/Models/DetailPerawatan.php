<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPerawatan extends Model
{
    use HasFactory;

    protected $table = 'detail_perawatan';
    protected $primaryKey = 'id_detail_perawatan';
    protected $fillable = ['nama_detail_perawatan'];
}