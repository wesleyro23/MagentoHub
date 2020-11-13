<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = "pedidos";

    protected $fillable = [
        'id',
        'numero_pedido_magento',
        'numero_pedido_datasul',
        'sincronizado_datasul',
        'sincronizado_magento',
        'pedido_valido_magento'
    ];
}
