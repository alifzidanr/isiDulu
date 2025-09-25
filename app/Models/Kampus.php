<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kampus extends Model
{
    use HasFactory;

    protected $table = 'kampus';
    protected $primaryKey = 'id_kampus';
    protected $fillable = ['nama_kampus'];

    public function units()
    {
        return $this->hasMany(Unit::class, 'id_kampus');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'id_kampus');
    }
}