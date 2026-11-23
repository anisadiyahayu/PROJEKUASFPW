<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_id')->unique()->constrained('billings'); // 1 billing : 0..1 laporan
            $table->foreignId('issued_by')->constrained('users');
            $table->string('report_number')->unique();
            $table->date('issue_date');
            // snapshot
            $table->string('ship_name');
            $table->string('hull_number');
            $table->string('owner_name');
            $table->text('owner_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technical_reports');
    }
};
