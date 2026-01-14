<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {

            // Cesta k uloženému súboru (storage path)
            if (!Schema::hasColumn('documents', 'file_path')) {
                $table->string('file_path')->nullable();
            }

            // Typ dokumentu (report, signed_agreement, company_report...)
            if (!Schema::hasColumn('documents', 'type')) {
                $table->string('type')->nullable();
            }

            // Kto dokument nahral (študent / firma)
            if (!Schema::hasColumn('documents', 'uploaded_by')) {
                $table->foreignId('uploaded_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            }

            // Stav dokumentu pre firmu (pending / approved / rejected)
            if (!Schema::hasColumn('documents', 'company_status')) {
                $table->string('company_status')
                    ->nullable()
                    ->default('pending');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'file_path')) {
                $table->dropColumn('file_path');
            }

            if (Schema::hasColumn('documents', 'type')) {
                $table->dropColumn('type');
            }

            if (Schema::hasColumn('documents', 'uploaded_by')) {
                $table->dropColumn('uploaded_by');
            }

            if (Schema::hasColumn('documents', 'company_status')) {
                $table->dropColumn('company_status');
            }
        });
    }
};
