<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $primaryKey = 'id_sub_sls';
    
    public $incrementing = false;
    protected $keyType = 'string';
}