<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('merchant_id')->index();
            $table->string("no_hp")->unique();
            $table->string("name", 100);
            $table->string("password", 100);
            $table->string("address")->nullable();
            $table->enum("role", ["staff", "manager", "administrator"]);
            $table->string("email")->nullable();
            $table->binary("photo")->nullable();
            $table->unsignedTinyInteger("flag")->default(0);
            $table->time("begun_at")->nullable();
            $table->time("exited_at")->nullable();
            $table->unsignedTinyInteger("active_work")->default(0);
            $table->string("device_no")->nullable();
            $table->string("user_agent")->nullable();
            $table->string("ip_address")->nullable();
            $table->timestamps();

            $table->foreign('merchant_id')
                ->references('id')
                ->on("merchants")
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
        Schema::dropIfExists('employees');
    }
}
