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
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum("expense_type", ["tarik_tunai", "belanja", "pinjaman", "kasbon", "lain"])
                ->after("type")->nullable();
            $table->unsignedTinyInteger("provider_id")->after('supplier_id')->index();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
