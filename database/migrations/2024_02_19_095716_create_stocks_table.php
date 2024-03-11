<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_id')->index();
            $table->unsignedInteger("qty")->default(0);
            $table->unsignedInteger("created_by")->index();
            $table->unsignedInteger("updated_by")->index()->nullable();
            $table->enum('type', ['in', 'out'])->default('in');

            $table->foreign('created_by')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');

            $table->foreign('updated_by')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
