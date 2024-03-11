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
        Schema::create('referrals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('saldo_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedInteger('merchant_id')->index();
            $table->string('phone', 20)->unique();
            $table->enum('status', ['pending', 'registered', 'withdrawn'])->index();
            $table->double("amount", 20, 2)->default("0.00");
            $table->timestamps();

            $table->foreign('merchant_id')
                ->references('id')
                ->on('merchants')->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')->onDelete('cascade');

            $table->foreign('saldo_id')
                ->references('id')
                ->on('saldos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referrals');
    }
};
