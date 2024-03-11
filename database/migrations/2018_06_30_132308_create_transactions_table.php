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
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_no', 30)->unique();
            $table->string('order_name', 50)->nullable();
            $table->date('work_date')->index();
            $table->integer('merchant_id')->unsigned()->index();
            $table->unsignedInteger("supplier_id")->nullable()->index();
            $table->unsignedInteger("customer_id")->nullable()->index();

            $table->enum('status', ['process', 'draft', 'success'])->nullable();
            $table->enum('type', ['income', 'expense'])->index();
            $table->enum('payment_method', ['cash', 'dana', 'debit_card', 'bca', 'ovo', 'shopee'])->index()->nullable();
            $table->enum('payment_status', ['paid', 'credit'])->index()->nullable();
            $table->unsignedInteger("employee_id")->index()->nullable();


            $table->date('due_date')->nullable();

            $table->timestamp("paid_at")->nullable();
            $table->timestamps();

            $table->foreign('merchant_id')
                ->references('id')
                ->on('merchants')
                ->onDelete('cascade');

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');

            $table->foreign("supplier_id")
                ->references('id')
                ->on("suppliers")
                ->onDelete("cascade");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
