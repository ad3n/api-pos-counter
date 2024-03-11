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
        Schema::create('merchants', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('name');
            $table->string('number', 20)->nullable();
            $table->string('address', 255)->nullable();
            // $table->enum('type', [
            //     'street_food',
            //     'resto',
            //     'grocery_store',
            //     'cafe'
            // ])->index()->nullable();
            $table->time('working_open_at')->nullable();
            $table->time('working_closed_at')->nullable();
            $table->tinyInteger('verified')->default("0");
            $table->unsignedSmallInteger('country_id')->index()->nullable();
            $table->char('province_id', 5)->index()->nullable()->collation('utf8mb4_unicode_ci');
            $table->char('regency_id', 8)->index()->nullable()->collation('utf8mb4_unicode_ci');

            $table->string("merchant_type", 50)->index()->nullable();
            $table->unsignedInteger("verified_by")->index()->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('merchant_type')
                ->references('code')
                ->on('merchant_types')
                ->onDelete('cascade');

            $table->foreign('verified_by')
                ->references('id')
                ->on('supers')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on("users")
                ->onDelete('cascade');

            $table->foreign('country_id')
                ->references('id')
                ->on("countries")
                ->onDelete('cascade');

            $table->foreign('province_id')
                ->references('id')
                ->on("provinces")
                ->onDelete('cascade');

            $table->foreign('regency_id')
                ->references('id')
                ->on("regencies")
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
        Schema::dropIfExists('merchants');
    }
};
