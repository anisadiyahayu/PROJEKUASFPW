<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penambahan aditif PRD 6.7 no. 3 dan no. 4 (3.10.4).
     *
     * Kolom ERD yang sudah ada tidak diubah. `faktor` lama sengaja tetap
     * dipertahankan sebagai hasil kali; dua kolom baru menyimpan masing-masing
     * faktor sesuai kontrak 3.10.4. Baris lama mendapat default 1 sehingga
     * subtotal dan total_biaya yang sudah tersimpan tidak berubah.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('opnams', 'jenis_kapal')) {
            Schema::table('opnams', function (Blueprint $table) {
                $table->string('jenis_kapal')->nullable()->after('catatan');
            });
        }

        // Backfill bersyarat: pada DB dev, sebagian migrasi lama tercatat Ran
        // tetapi kolomnya tidak ada. Tidak ada efek pada DB yang sudah punya kolom.
        if (! Schema::hasColumn('opnam_items', 'faktor')) {
            Schema::table('opnam_items', function (Blueprint $table) {
                $table->decimal('faktor', 5, 2)->default(1);
            });
        }

        if (! Schema::hasColumn('opnam_items', 'ketebalan')) {
            Schema::table('opnam_items', function (Blueprint $table) {
                $table->integer('ketebalan')->nullable();
            });
        }

        if (! Schema::hasColumn('opnam_items', 'kompleksitas')) {
            Schema::table('opnam_items', function (Blueprint $table) {
                $table->string('kompleksitas')->default('low');
            });
        }

        if (! Schema::hasColumn('opnam_items', 'catatan_tambahan')) {
            Schema::table('opnam_items', function (Blueprint $table) {
                $table->text('catatan_tambahan')->nullable();
            });
        }

        if (! Schema::hasColumn('opnam_items', 'faktor_ketebalan')) {
            Schema::table('opnam_items', function (Blueprint $table) {
                $table->decimal('faktor_ketebalan', 5, 2)->default(1);
            });
        }

        if (! Schema::hasColumn('opnam_items', 'faktor_kompleksitas')) {
            Schema::table('opnam_items', function (Blueprint $table) {
                $table->decimal('faktor_kompleksitas', 5, 2)->default(1);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('opnam_items', 'faktor_kompleksitas')
            || Schema::hasColumn('opnam_items', 'faktor_ketebalan')) {
            Schema::table('opnam_items', function (Blueprint $table) {
                if (Schema::hasColumn('opnam_items', 'faktor_kompleksitas')) {
                    $table->dropColumn('faktor_kompleksitas');
                }
                if (Schema::hasColumn('opnam_items', 'faktor_ketebalan')) {
                    $table->dropColumn('faktor_ketebalan');
                }
            });
        }

        if (Schema::hasColumn('opnams', 'jenis_kapal')) {
            Schema::table('opnams', function (Blueprint $table) {
                $table->dropColumn('jenis_kapal');
            });
        }
    }
};
