<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_id')->constrained('billings');
            $table->foreignId('created_by')->constrained('users');
            $table->string('adjustment_type'); // diskon, markup
            $table->decimal('adjustment_value', 15, 2);
            $table->decimal('adjustment_amount', 15, 2);
            $table->text('disposition_note');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_adjustments');
    }
};
