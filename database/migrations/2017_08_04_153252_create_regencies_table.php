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
        Schema::create('regencies', function (Blueprint $table) {
            $table->char('id', 8)->primary()->collation('utf8mb4_unicode_ci');
            $table->char('province_id', 5)->index()->collation('utf8mb4_unicode_ci');
            $table->string('name', 255)->collation('utf8mb4_unicode_ci');
            $table->timestamp("created_at")->useCurrent();

            $table->foreign('province_id')
                ->references('id')
                ->on("provinces")
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
        Schema::dropIfExists('regencies');
    }
};
