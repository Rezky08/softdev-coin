<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinSecurityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('dbmarketcoins')->create('coin_securitys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('coin_id')->unsigned();
            $table->string('coin_username', 100)->unique();
            $table->string('coin_question1', 100)->nullable();
            $table->string('coin_answer1', 100)->nullable();
            $table->string('coin_question2', 100)->nullable();
            $table->string('coin_answer2', 100)->nullable();
            $table->boolean('coin_status')->default(0);
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
        Schema::connection('dbmarketcoins')->dropIfExists('coin_securitys');
    }
}
