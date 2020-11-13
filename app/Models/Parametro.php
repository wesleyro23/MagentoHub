<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametro extends Model
{
    protected $table = "parametros";

    protected $fillable = [
        'id',
        'login_magento',
        'senha_magento',
        'conexaoMagento',
        'tipo_cadprod_magento',
        'attributeSetList_magento',
        'store_view_magento',
        'Loja',
        'tabela_preco_erp',
        'login_erp',
        'senha_erp',
        'conexaoDatasul',
        'codigo_transportador',
        'status_pedido_integracao',
        'grupo_tabelas_parceiros',
        'tipo_conssku_magento',
        'email'
    ];
}
