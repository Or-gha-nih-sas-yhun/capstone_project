<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('puroks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Adopt whatever puroks the residents table already contains, so no
        // existing record ends up pointing at a purok that is not on the list.
        if (Schema::hasTable('residents')) {
            $existing = DB::table('residents')
                ->whereNotNull('purok')
                ->where('purok', '<>', '')
                ->distinct()
                ->pluck('purok');

            $now = now();
            $rows = [];
            $seen = [];

            foreach ($existing as $i => $name) {
                $name = trim($name);
                $fold = mb_strtolower($name);

                if ($name === '' || isset($seen[$fold])) {
                    continue;
                }

                $seen[$fold] = true;
                $rows[] = [
                    'name'       => $name,
                    'sort_order' => $i,
                    'status'     => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows) {
                DB::table('puroks')->insert($rows);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('puroks');
    }
};
