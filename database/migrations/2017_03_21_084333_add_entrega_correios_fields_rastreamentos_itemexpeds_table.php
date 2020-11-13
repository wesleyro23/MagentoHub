<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEntregaCorreiosFieldsRastreamentosItemexpedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rastreamentos_itemexpeds', function (Blueprint $table) {
            $table->integer('entrega_correios')->after('email_enviado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rastreamentos_itemexpeds', function (Blueprint $table) {
            $table->dropColumn('entrega_correios');
        });
    }
}
