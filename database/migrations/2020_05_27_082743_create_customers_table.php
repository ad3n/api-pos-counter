<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name", 100)->index();
            $table->string("no_hp", 25)->index()->unique();
            $table->string("no_hp_2", 25)->nullable();
            $table->string("no_hp_3", 25)->nullable();
            $table->string("pln_token", 25)->nullable();
            $table->string("bpjs", 25)->nullable();
            $table->string("gopay_va", 25)->nullable();
            $table->string("maxim_id", 25)->nullable();
            $table->string("dana_va", 25)->nullable();
            $table->string("ovo_va", 25)->nullable();;
            $table->string("shopee_va", 25)->nullable();
            $table->string("email")->nullable()->index();
            $table->string("address", 200)->nullable();
            $table->mediumText("note");
            $table->unsignedInteger("created_by");
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
        Schema::dropIfExists('customers');
    }
};
