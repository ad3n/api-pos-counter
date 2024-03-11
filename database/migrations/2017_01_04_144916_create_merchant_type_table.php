<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_types', function (Blueprint $table) {
            $table->string('code', 50)->primary();
            $table->string('name');
            $table->unsignedInteger("created_by")->index()->nullable();
            $table->unsignedInteger("last_updated_by")->index()->nullable();
            $table->foreign('created_by')
                ->references('id')
                ->on('supers')
                ->onDelete('cascade');


            $table->foreign('last_updated_by')
                ->references('id')
                ->on('supers')
                ->onDelete('cascade');

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
        Schema::dropIfExists('merchant_types');
    }
}
