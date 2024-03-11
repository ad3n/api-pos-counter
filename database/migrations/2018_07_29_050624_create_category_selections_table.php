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
        Schema::create('category_selections', function (Blueprint $table) {

            $table->unsignedInteger('merchant_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('category_id');
            $table->timestamps();

            $table->primary(['merchant_id', 'product_id', 'category_id']);

            $table->foreign('merchant_id')
                ->references('id')
                ->on('merchants')->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_selections');
    }
};
