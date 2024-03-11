<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('merchant_id')->index();
            $table->string("name", 255);
            $table->string("address", 255);
            $table->string("phone", 20);
            $table->string("telp", 20);
            $table->unsignedSmallInteger('country_id')->index()->nullable();
            $table->char('province_id', 5)->index()->nullable()->collation('utf8mb4_unicode_ci');
            $table->char('regency_id', 8)->index()->nullable()->collation('utf8mb4_unicode_ci');

            $table->string('sales_person')->nullable();
            $table->string('sales_contact', 20)->nullable();

            $table->timestamps();

            $table->foreign('merchant_id')
                ->references('id')
                ->on('merchants')
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
        Schema::dropIfExists('suppliers');
    }
};
