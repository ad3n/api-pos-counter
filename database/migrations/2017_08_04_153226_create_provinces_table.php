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
        Schema::create('provinces', function (Blueprint $table) {
            $table->char('id', 5)->primary()->collation('utf8mb4_unicode_ci');
            $table->unsignedSmallInteger('country_id')->index();
            $table->string('name', 255)->collation('utf8mb4_unicode_ci');
            $table->timestamp("created_at")->useCurrent();

            $table->foreign('country_id')
                ->references('id')
                ->on("countries")
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
        Schema::dropIfExists('provinces');
    }
};
