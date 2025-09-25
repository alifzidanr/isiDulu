<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Permohonan extends Model
{
    use HasFactory;

    protected $table = 'permohonan';
    protected $primaryKey = 'id_permohonan';
    protected $fillable = [
        'tanggal',
        'nama_pemohon',
        'email_pemohon',
        'kontak_pemohon',
        'pimpinan_pemohon',
        'id_unit',
        'id_sub_unit',
        'inventaris',
        'keluhan',
        'id_user',
        'status_permohonan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    const STATUS_PERMOHONAN = 0;
    const STATUS_DIKERJAKAN = 1;
    const STATUS_SELESAI = 2;
    const STATUS_DIARSIPKAN = 3;
    const STATUS_DISAHKAN = 4;
    const STATUS_DIBATALKAN = 5;

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit');
    }

    public function subUnit()
    {
        return $this->belongsTo(SubUnit::class, 'id_sub_unit');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function getStatusTextAttribute()
    {
        return match($this->status_permohonan) {
            0 => 'Permohonan',
            1 => 'Dikerjakan',
            2 => 'Selesai',
            3 => 'Diarsipkan',
            4 => 'Disahkan',
            5 => 'Dibatalkan',
            default => 'Unknown'
        };
    }

    public static function boot()
    {
        parent::boot();
        
        // Auto-archive completed requests after 1 month
        static::updating(function ($model) {
            if (in_array($model->status_permohonan, [2, 4, 5])) {
                $oneMonthAgo = Carbon::now()->subMonth();
                if ($model->updated_at <= $oneMonthAgo) {
                    $model->status_permohonan = 3;
                }
            }
        });
    }
}