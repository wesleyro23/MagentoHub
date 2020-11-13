<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParametrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parametros', function (Blueprint $table) {
            $table->increments('id');
            $table->string('login_magento', 30);
            $table->string('senha_magento', 100);
            $table->string('tipo_cadprod_magento', 100);
            $table->string('attributeSetList_magento', 20);
            $table->string('store_view_magento', 50);
            $table->string('tabela_preco_erp', 100);
            $table->string('login_erp', 100);
            $table->string('senha_erp', 100);
            $table->string('codigo_transportador');
            $table->string('status_pedido_integracao', 100);
            $table->string('grupo_tabelas_parceiros', 20);
            $table->string('tipo_conssku_magento',20);
            $table->string('email',40);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('parametros');
    }
}
