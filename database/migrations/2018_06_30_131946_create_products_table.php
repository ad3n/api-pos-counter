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
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 50)->nullable()->index();
            $table->string('name')->nullable();
            $table->enum('type', ['piece', 'saldo', 'volume'])->default('piece');
            $table->unsignedInteger('merchant_id')->index();
            $table->unsignedInteger('supplier_id')->index()->nullable();
            $table->double('regular_price', 20, 2)->nullable();
            $table->double('sale_price', 20, 2)->nullable();
            $table->tinyInteger('on_sale')->default("0");
            $table->unsignedBigInteger("qty")->default(0);
            $table->string('photo')->nullable();
            $table->double('capital_cost', 20, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('merchant_id')
                ->references('id')
                ->on('merchants')
                ->onDelete('cascade');

            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
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
        Schema::dropIfExists('products');
    }
};
