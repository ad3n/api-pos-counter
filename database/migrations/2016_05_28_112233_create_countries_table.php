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
        Schema::create('countries', function (Blueprint $table) {
            $table->smallIncrements("id");
            $table->char('iso_code', 2)->unique();
            $table->string('name');

            $table->string("idd_code", 5)->nullable()->index();
            $table->string("timezone", 50)->nullable();
            $table->string("locale", 10)->nullable();

            $table->timestamp("created_at")->useCurrent();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('countries');
    }
};
