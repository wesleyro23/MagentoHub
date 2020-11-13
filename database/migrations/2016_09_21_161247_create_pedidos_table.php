<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('numero_pedido_magento', 30);
            $table->string('numero_pedido_datasul', 30);
            $table->integer('sincronizado_datasul');
            $table->integer('sincronizado_magento');
            $table->integer('pedido_valido_magento');
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
        Schema::drop('pedidos');
    }
}
