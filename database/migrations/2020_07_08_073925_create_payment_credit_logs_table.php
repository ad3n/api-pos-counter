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
        Schema::create('payment_credit_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string("order_no")->index();
            $table->unsignedInteger("merchant_id")->index();
            $table->unsignedInteger("employee_id")->index();
            $table->unsignedInteger("customer_id")->index();
            $table->string("type");
            $table->double("total")->default(0);
            $table->string("payment_method")->nullable();
            $table->string("payment_status")->nullable();
            $table->timestamp("purchased_at");
            $table->timestamp("paid_at");
            $table->mediumText("note")->nullable();
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
        Schema::dropIfExists('payment_credit_logs');
    }
};
