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
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();

            $table->unsignedInteger("created_by")->index()->nullable();

            $table->foreign('created_by')
                ->references('id')
                ->on('supers')
                ->onDelete('cascade');

            $table->unsignedInteger("last_updated_by")->index()->nullable();

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
        Schema::dropIfExists('categories');
    }
};
