<?php

declare(strict_types=1);

use App\Models\Shop\ShopCategory;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        ShopCategory::query()
            ->where('title', 'Coeliac Gluten Free Travel Cards')
            ->update([
                'description' => 'Travel safely with my double sided, extra thick, pre-printed Coeliac travel cards. Each card has two languages, perfect for dining gluten free in Spain, Egypt, Greece and beyond. Designed for Coeliacs without other allergies, they’re durable, compact, and make communicating your needs easy for stress free meals abroad.',
            ]);

        ShopCategory::query()
            ->where('title', 'Coeliac+ Other Allergen Travel Cards')
            ->update([
                'description' => 'Travel safely with my Coeliac+ cards, which clearly explain what to avoid as a Coeliac and let you mark extra allergies or dietary requirements. Perfect for dining out in France, Italy, Turkey and beyond, they’re double-sided, durable, and help ensure stress free gluten free and allergen safe meals abroad.',
            ]);

        ShopCategory::query()
            ->where('title', 'Gluten Free Stickers')
            ->update([
                'description' => 'Label safely with my gluten free stickers, waterproof and freezer proof 3cm rounds in red, blue, green or mixed packs. Perfect for marking food products, labelling toasters, utensils or kitchen areas to avoid cross-contamination. Clear, bold and durable, essential for any Coeliac-friendly kitchen or business.',
            ]);
    }
};
