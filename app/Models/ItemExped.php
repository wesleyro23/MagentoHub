<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemExped extends Model
{
    protected $table = 'item_expeds';

    //Primary Key da Tabela.
    protected $primaryKey = 'id';

    protected $fillable = [
        'sku',
        'qtd',
    ];

    public function rastreamentos()
    {
        return $this->belongsToMany('App\Models\Rastreamento', 'rastreamentos_itemexpeds', 'id_item_exped', 'id_rastreamento')
            ->withPivot(['cod_entrega','key_xml','sincronizado','nr_ped_datasul','nr_ped_magento']);
        
    }

}
