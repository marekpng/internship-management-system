<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('notify_new_request')->default(true);
        $table->boolean('notify_approved')->default(true);
        $table->boolean('notify_rejected')->default(true);
        $table->boolean('notify_profile_change')->default(true);
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn([
            'notify_new_request',
            'notify_approved',
            'notify_rejected',
            'notify_profile_change',
        ]);
    });
}
};
