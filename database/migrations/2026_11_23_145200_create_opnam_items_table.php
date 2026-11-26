<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opnam_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opnam_id')->constrained('opnams');
            $table->foreignId('tariff_id')->constrained('tariffs');
            $table->decimal('volume', 15, 2);
            $table->decimal('unit_price', 15, 2); // snapshot
            $table->decimal('subtotal', 15, 2); // snapshot
            $table->text('description')->nullable();
            $table->integer('sort_order');
            $table->string('lokasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opnam_items');
    }
};
