<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tariffs', function (Blueprint $table) {
            $table->unique(
                ['material_spec_id', 'unit_id', 'effective_from'],
                'tariffs_material_spec_unit_effective_from_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('tariffs', function (Blueprint $table) {
            $table->dropUnique('tariffs_material_spec_unit_effective_from_unique');
        });
    }
};
