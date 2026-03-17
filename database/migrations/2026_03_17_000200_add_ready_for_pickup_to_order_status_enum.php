<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending', 'preparing', 'ready_for_pickup', 'completed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        DB::table('orders')
            ->where('status', 'ready_for_pickup')
            ->update(['status' => 'preparing']);

        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending', 'preparing', 'completed') NOT NULL DEFAULT 'pending'");
    }
};
