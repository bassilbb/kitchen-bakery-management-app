<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->foreignId('production_request_id')
                ->nullable()
                ->after('user_id')
                ->constrained('production_requests')
                ->nullOnDelete();
            $table->decimal('wastage', 12, 3)->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('production_request_id');
            $table->dropColumn('wastage');
        });
    }
};
