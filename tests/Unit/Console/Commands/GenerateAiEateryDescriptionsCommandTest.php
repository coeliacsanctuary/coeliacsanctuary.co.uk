<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Ai\Agents\EateryDescriptionAgent;
use App\Console\Commands\GenerateAiEateryDescriptionsCommand;
use App\Models\EateryAiDescription;
use App\Models\EatingOut\Eatery;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenerateAiEateryDescriptionsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        EateryDescriptionAgent::fake(['Generated description']);
    }

    #[Test]
    public function itPromptsTheAgentForEachEatery(): void
    {
        $this->create(Eatery::class, ['generated_ai_description' => false]);
        $this->create(Eatery::class, ['generated_ai_description' => false]);

        $this->artisan(GenerateAiEateryDescriptionsCommand::class);

        $this->assertDatabaseCount(EateryAiDescription::class, 2);
    }

    #[Test]
    public function itSavesTheGeneratedDescription(): void
    {
        EateryDescriptionAgent::fake(['The generated description.']);

        $eatery = $this->create(Eatery::class, ['generated_ai_description' => false]);

        $this->artisan(GenerateAiEateryDescriptionsCommand::class);

        $description = EateryAiDescription::query()->where('wheretoeat_id', $eatery->id)->first();

        $this->assertNotNull($description);
        $this->assertEquals('The generated description.', $description->description);
    }

    #[Test]
    public function itSetsGeneratedAiDescriptionToTrue(): void
    {
        $eatery = $this->create(Eatery::class, ['generated_ai_description' => false]);

        $this->artisan(GenerateAiEateryDescriptionsCommand::class);

        $this->assertTrue((bool) $eatery->refresh()->generated_ai_description);
    }

    #[Test]
    public function itSkipsEateriesWithExistingAiDescription(): void
    {
        $this->create(Eatery::class, ['generated_ai_description' => true]);

        $this->artisan(GenerateAiEateryDescriptionsCommand::class);

        EateryDescriptionAgent::assertNeverPrompted();
        $this->assertDatabaseEmpty(EateryAiDescription::class);
    }

    #[Test]
    public function itPromptsWithTheCorrectPromptText(): void
    {
        $this->create(Eatery::class, ['generated_ai_description' => false]);

        $this->artisan(GenerateAiEateryDescriptionsCommand::class);

        EateryDescriptionAgent::assertPrompted('Generate the description');
    }
}
