<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConexaoFieldsParametrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parametros', function (Blueprint $table) {
            $table->string('conexaoMagento', 255)->after('senha_magento');
            $table->string('conexaoDatasul', 255)->after('senha_erp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parametros', function (Blueprint $table) {
            $table->dropColumn('conexaoMagento');
            $table->dropColumn('conexaoDatasul');
        });
    }
}
