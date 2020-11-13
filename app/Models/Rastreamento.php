<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rastreamento extends Model
{
    protected $table = 'rastreamentos';

    //Primary Key da Tabela.
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'cod_rastreamento'
    ];

    public function itemexpeds()
    {
        return $this->belongsToMany('App\Models\ItemExped', 'rastreamentos_itemexpeds', 'id_rastreamento', 'id_item_exped');
    }
}
