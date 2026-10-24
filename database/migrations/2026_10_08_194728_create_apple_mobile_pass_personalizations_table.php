<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('apple_mobile_pass_personalizations', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('mobile_pass_id')
                ->unique()
                ->constrained('mobile_passes')
                ->cascadeOnDelete();

            $table->string('description');
            $table->json('required_fields');
            $table->text('terms_and_conditions')->nullable();

            $table->string('personalization_token')->nullable();
            $table->json('submitted_info')->nullable();
            $table->timestamp('personalized_at')->nullable();

            $table->timestamps();
        });
    }
};
