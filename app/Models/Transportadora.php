<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transportadora extends Model
{
    protected $table = "transportadoras";

    protected $fillable = [
        'id',
        'codigo_magento',
        'codigo_datasul'
    ];
}
