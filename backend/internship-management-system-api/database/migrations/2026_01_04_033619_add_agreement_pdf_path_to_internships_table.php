<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            if (!Schema::hasColumn('internships', 'agreement_pdf_path')) {
                $table->string('agreement_pdf_path')->nullable()->after('company_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            if (Schema::hasColumn('internships', 'agreement_pdf_path')) {
                $table->dropColumn('agreement_pdf_path');
            }
        });
    }
};
