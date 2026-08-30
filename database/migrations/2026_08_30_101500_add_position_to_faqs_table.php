<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table): void {
            $table->unsignedInteger('position')->after('faqable_id')->default(0);
        });

        DB::statement('
            UPDATE faqs
            JOIN (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY faqable_type, faqable_id ORDER BY id) - 1 AS resolved_position
                FROM faqs
            ) AS ordered ON ordered.id = faqs.id
            SET faqs.position = ordered.resolved_position
        ');
    }
};
