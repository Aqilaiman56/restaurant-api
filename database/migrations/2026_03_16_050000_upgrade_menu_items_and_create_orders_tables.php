<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menu_items')) {
            $missingName = ! Schema::hasColumn('menu_items', 'name');
            $missingDescription = ! Schema::hasColumn('menu_items', 'description');
            $missingPrice = ! Schema::hasColumn('menu_items', 'price');
            $missingAvailability = ! Schema::hasColumn('menu_items', 'availability');

            if ($missingName || $missingDescription || $missingPrice || $missingAvailability) {
                Schema::table('menu_items', function (Blueprint $table) use ($missingName, $missingDescription, $missingPrice, $missingAvailability) {
                    if ($missingName) {
                        $table->string('name')->after('id');
                    }

                    if ($missingDescription) {
                        $table->text('description')->nullable()->after('name');
                    }

                    if ($missingPrice) {
                        $table->decimal('price', 10, 2)->default(0)->after('description');
                    }

                    if ($missingAvailability) {
                        $table->enum('availability', ['available', 'unavailable'])->default('available')->after('price');
                    }
                });
            }
        }

        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->enum('status', ['pending', 'preparing', 'completed'])->default('pending');
                $table->decimal('total_price', 10, 2);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('menu_item_id')->nullable()->constrained('menu_items')->nullOnDelete();
                $table->unsignedInteger('quantity');
                $table->decimal('price', 10, 2);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        if (Schema::hasTable('menu_items')) {
            $hasName = Schema::hasColumn('menu_items', 'name');
            $hasDescription = Schema::hasColumn('menu_items', 'description');
            $hasPrice = Schema::hasColumn('menu_items', 'price');
            $hasAvailability = Schema::hasColumn('menu_items', 'availability');

            if ($hasName || $hasDescription || $hasPrice || $hasAvailability) {
                Schema::table('menu_items', function (Blueprint $table) use ($hasName, $hasDescription, $hasPrice, $hasAvailability) {
                    $columns = array_values(array_filter([
                        $hasName ? 'name' : null,
                        $hasDescription ? 'description' : null,
                        $hasPrice ? 'price' : null,
                        $hasAvailability ? 'availability' : null,
                    ]));

                    if ($columns !== []) {
                        $table->dropColumn($columns);
                    }
                });
            }
        }
    }
};