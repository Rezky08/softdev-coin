<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinLoginTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('dbmarketcoins')->create('coin_logins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('coin_id')->unsigned();
            $table->string('coin_username', 100)->unique();
            $table->text('coin_password');
            $table->boolean('coin_status')->default(0)->comment('0 Banned, 1 Active');
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
        Schema::connection('dbmarketcoins')->dropIfExists('coin_logins');
    }
}
