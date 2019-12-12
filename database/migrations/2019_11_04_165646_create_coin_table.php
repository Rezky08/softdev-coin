<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('dbmarketcoins')->create('coin_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('coin_fullname', 100);
            $table->date('coin_dob')->nullable();
            $table->text('coin_address')->nullable();
            $table->boolean('coin_sex')->default(0)->comment('0 Female, 1 Male');
            $table->string('coin_email', 100);
            $table->string('coin_phone', 20)->nullable();
            $table->string('coin_username', 100)->unique();
            $table->integer('coin_account_type')->unsigned();
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
        Schema::connection('dbmarketcoins')->dropIfExists('coin_details');
    }
}
