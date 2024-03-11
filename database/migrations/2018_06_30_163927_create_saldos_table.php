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
        Schema::create('saldos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('receipt_no', 20);
            $table->double('amount',20,2);
            $table->integer('merchant_id')->unsigned()->nullable();
            $table->enum('type', ['deposit', 'referral_bonus', 'free'])->index();
            $table->enum('payment_method', ['cash', 'bank_transfer', 'bonus'])->index()->nullable();
            $table->enum('status', ['pending', 'ok'])->index();
            $table->mediumText("payment_data")->nullable();
            $table->mediumText("note")->nullable();

            $table->double('usage', 20, 2)->default("0.00");
            $table->timestamp('closed_at')->nullable();
            $table->timestamp("paid_at")->nullable();

            $table->unsignedInteger("admin_ok_by")->index()->nullable();
            $table->unsignedInteger("admin_failed_by")->index()->nullable();
            $table->string("note_failed", 255)->nullable();

            $table->foreign('admin_ok_by')
                ->references('id')
                ->on('supers')
                ->onDelete('cascade');

            $table->foreign('admin_failed_by')
                ->references('id')
                ->on('supers')
                ->onDelete('cascade');

            $table->timestamps();

            $table->foreign('merchant_id')
                ->references('id')
                ->on('merchants')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saldos');
    }
};
