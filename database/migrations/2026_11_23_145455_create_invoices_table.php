<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_id')->unique()->constrained('billings'); // 1 billing : 0..1 invoice
            $table->foreignId('issued_by')->constrained('users');
            $table->string('invoice_number')->unique();
            $table->date('issue_date');
            // snapshot
            $table->string('ship_name');
            $table->string('hull_number');
            $table->string('owner_name');
            $table->text('owner_address')->nullable();
            $table->decimal('subtotal_amount', 15, 2);
            $table->decimal('adjustment_amount', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->integer('revision_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
