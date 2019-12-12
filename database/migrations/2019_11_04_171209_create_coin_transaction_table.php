<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('dbmarketcoins')->create('coin_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('coin_id_source')->unsigned();
            $table->bigInteger('coin_id_destination')->unsigned();
            $table->tinyInteger('coin_transaction_code');
            $table->bigInteger('coin_balance');
            $table->boolean('coin_transaction_type')->comment('0 credit,1 debit');
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
        Schema::connection('dbmarketcoins')->dropIfExists('coin_transactions');
    }
}
