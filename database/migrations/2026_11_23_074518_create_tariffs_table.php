<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_spec_id')->constrained('material_specs');
            $table->foreignId('unit_id')->constrained('units');
            $table->foreignId('created_by')->constrained('users');
            $table->decimal('price', 15, 2);
            $table->date('effective_from');
            $table->dateTime('created_at')->nullable(); // hanya created_at (riwayat tarif tidak diubah)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
