<?php

declare(strict_types=1);

use App\Models\Blogs\Blog;
use App\Models\Recipes\Recipe;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Blog::withoutGlobalScopes()->whereNotNull('faqs')->cursor()->each(function (Blog $blog): void {
            $faqs = json_decode($blog->getRawOriginal('faqs'), true) ?? [];

            foreach ($faqs as $faq) {
                DB::table('faqs')->insert([
                    'faqable_type' => Blog::class,
                    'faqable_id' => $blog->id,
                    'question' => $faq['fields']['question'],
                    'answer' => $faq['fields']['answer'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        Recipe::withoutGlobalScopes()->whereNotNull('faqs')->cursor()->each(function (Recipe $recipe): void {
            $faqs = json_decode($recipe->getRawOriginal('faqs'), true) ?? [];

            foreach ($faqs as $faq) {
                DB::table('faqs')->insert([
                    'faqable_type' => Recipe::class,
                    'faqable_id' => $recipe->id,
                    'question' => $faq['fields']['question'],
                    'answer' => $faq['fields']['answer'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        Schema::table('blogs', function (Blueprint $table): void {
            $table->dropColumn('faqs');
        });

        Schema::table('recipes', function (Blueprint $table): void {
            $table->dropColumn('faqs');
        });
    }
};
