<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinBalanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('dbmarketcoins')->create('coin_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('coin_id')->unsigned();
            $table->string('coin_username', 100)->unique();
            $table->bigInteger('coin_balance')->default(0);
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
        Schema::connection('dbmarketcoins')->dropIfExists('coin_balances');
    }
}
