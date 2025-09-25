<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubUnit extends Model
{
    use HasFactory;

    protected $table = 'sub_unit';
    protected $primaryKey = 'id_sub_unit';
    protected $fillable = ['nama_sub_unit', 'id_unit'];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit');
    }

    public function permohonans()
    {
        return $this->hasMany(Permohonan::class, 'id_sub_unit');
    }
}