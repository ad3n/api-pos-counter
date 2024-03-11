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
        Schema::create('transaction_saldos', function (Blueprint $table) {
            // $table->unsignedInteger('transaction_item_id');
            // $table->unsignedInteger('saldo_id');
            // $table->double("amount", 20, 2);
            // $table->timestamps();

            // $table->primary([
            //     'transaction_item_id',
            //     'saldo_id'
            // ]);

            // $table->foreign('transaction_item_id')
            //     ->references('id')
            //     ->on('transaction_items')->onDelete('cascade');

            // $table->foreign('saldo_id')
            //     ->references('id')
            //     ->on('saldos')->onDelete('cascade');
            $table->unsignedInteger('transaction_id');
            $table->unsignedInteger('saldo_id');
            $table->double("amount", 20, 2);
            $table->timestamps();

            $table->primary([
                'transaction_id',
                'saldo_id'
            ]);

            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')->onDelete('cascade');

            $table->foreign('saldo_id')
                ->references('id')
                ->on('saldos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_saldo_items');
    }
};
