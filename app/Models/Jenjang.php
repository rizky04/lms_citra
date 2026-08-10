<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jenjang extends Model
{
    // Master global — tanpa BelongsToSekolah.
    protected $fillable = ['nama'];
}
