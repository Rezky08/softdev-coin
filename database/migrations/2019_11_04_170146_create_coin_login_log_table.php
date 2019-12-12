<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinLoginLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('dbmarketcoins')->create('coin_login_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('coin_id')->unsigned();
            $table->string('coin_username', 100);
            $table->boolean('login_success')->default(0)->comment('0 Fail, 1 Success');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('dbmarketcoins')->dropIfExists('coin_login_logs');
    }
}
