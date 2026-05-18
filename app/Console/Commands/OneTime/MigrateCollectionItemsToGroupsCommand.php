<?php

declare(strict_types=1);

namespace App\Console\Commands\OneTime;

use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\progress;

class MigrateCollectionItemsToGroupsCommand extends Command
{
    protected $signature = 'one-time:migrate-collection-items-to-groups';

    protected int $groupsCreated = 0;

    protected int $itemsMigrated = 0;

    public function handle(): void
    {
        $collections = Collection::withoutGlobalScopes()->get();

        progress(
            label: 'Migrating collection items to groups',
            steps: $collections,
            callback: $this->processCollection(...),
        );

        $this->line("Groups created: {$this->groupsCreated}");
        $this->line("Items migrated: {$this->itemsMigrated}");
    }

    protected function processCollection(Collection $collection): void
    {
        $group = CollectionGroup::create([
            'collection_id' => $collection->id,
            'title' => null,
            'body' => null,
            'position' => 1,
            'created_at' => $collection->created_at,
            'updated_at' => $collection->updated_at,
        ]);

        ++$this->groupsCreated;

        DB::table('collection_items')
            ->where('collection_id', $collection->id)
            ->get()
            ->each(function (object $item) use ($group): void {
                DB::table('collection_group_items')->insert([
                    'collection_group_id' => $group->id,
                    'item_id' => $item->item_id,
                    'item_type' => $item->item_type,
                    'item_title' => null,
                    'item_description' => null,
                    'position' => $item->position,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ]);

                ++$this->itemsMigrated;
            });
    }
}
