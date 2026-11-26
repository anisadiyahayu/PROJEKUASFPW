<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opnams', function (Blueprint $table) {
            $table->decimal('adjustment_amount', 15, 2)->default(0)->after('lokasi');
        });
    }

    public function down(): void
    {
        Schema::table('opnams', function (Blueprint $table) {
            $table->dropColumn('adjustment_amount');
        });
    }
};
