<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'unit';
    protected $primaryKey = 'id_unit';
    protected $fillable = ['nama_unit', 'id_kampus'];

    public function kampus()
    {
        return $this->belongsTo(Kampus::class, 'id_kampus');
    }

    public function subUnits()
    {
        return $this->hasMany(SubUnit::class, 'id_unit');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'id_unit');
    }

    public function permohonans()
    {
        return $this->hasMany(Permohonan::class, 'id_unit');
    }
}