<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KuisSoal extends Model
{
    // Pivot — biasanya diakses lewat relasi Kuis::soal(). Model ini jarang dipakai langsung.
    public $timestamps = false;

    protected $table = 'kuis_soals';

    protected $fillable = ['kuis_id', 'soal_id', 'urutan'];
}
