<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'id_sub_sls', 'id_sub_sls');
    }
}