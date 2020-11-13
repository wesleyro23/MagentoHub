<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRestreamentoItemexpedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rastreamentos_itemexpeds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_rastreamento')->unsigned();
            $table->foreign('id_rastreamento')->references('id')->on('rastreamentos');
            $table->integer('id_item_exped')->unsigned();
            $table->foreign('id_item_exped')->references('id')->on('item_expeds');
            $table->string('cod_entrega',20);
            $table->string('key_xml',50);
            $table->integer('sincronizado');
            $table->integer('email_enviado');
            $table->string('nr_ped_datasul', 20);
            $table->string('nr_ped_magento', 20);
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
        Schema::drop('rastreamentos_itemexpeds');
    }
}
