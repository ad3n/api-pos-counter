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
        Schema::create('supers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('role_id')->index();
            $table->string('phone', 20)->unique();
            $table->string('name' , 100);
            $table->string('email', 50)->unique()->nullable();
            $table->string('password', 100);

            $table->unsignedTinyInteger("active")->default(1);
            $table->unsignedTinyInteger("flag")->default(0);

            $table->timestamp("last_login")->nullable();

            $table->rememberToken();
            $table->timestamps();

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

        });

        Schema::table("users", function(Blueprint $table) {
            $table->unsignedInteger("activated_by")->index()->nullable()->after("active");

            $table->foreign('activated_by')
                ->references('id')
                ->on('supers')
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
        Schema::dropIfExists('supers');
    }
};
