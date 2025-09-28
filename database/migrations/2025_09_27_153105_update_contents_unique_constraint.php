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
        Schema::table('contents', function (Blueprint $table) {
            // Drop the old unique constraint on provider_id
            $table->dropUnique(['provider_id']);
            
            // Add a composite unique constraint on provider_id and provider_name
            $table->unique(['provider_id', 'provider_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique(['provider_id', 'provider_name']);
            
            // Restore the old unique constraint on provider_id
            $table->unique('provider_id');
        });
    }
};
