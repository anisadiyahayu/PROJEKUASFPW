<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opnams', function (Blueprint $table) {
            $table->id();
            $table->string('opnam_number')->unique();
            $table->foreignId('ship_id')->constrained('ships');
            $table->foreignId('created_by')->constrained('users');
            // draft, menunggu_review, perlu_revisi, menunggu_approval_direksi, ditolak, disetujui, dokumen_terbit
            $table->string('status')->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('forwarded_at')->nullable();
            $table->date('tanggal_opnam')->nullable();
            $table->text('catatan')->nullable();
            $table->decimal('total_biaya', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opnams');
    }
};
