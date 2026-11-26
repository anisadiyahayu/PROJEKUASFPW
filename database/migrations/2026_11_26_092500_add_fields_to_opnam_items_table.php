<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opnam_items', function (Blueprint $table) {
            $table->integer('ketebalan')->nullable()->after('faktor');
            $table->string('kompleksitas')->default('low')->after('ketebalan');
            $table->text('catatan_tambahan')->nullable()->after('kompleksitas');
        });
    }

    public function down(): void
    {
        Schema::table('opnam_items', function (Blueprint $table) {
            $table->dropColumn(['ketebalan', 'kompleksitas', 'catatan_tambahan']);
        });
    }
};
