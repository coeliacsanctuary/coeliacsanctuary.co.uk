<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\TripSummaryAgent;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Laravel\Ai\ObjectSchema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TripSummaryAgentTest extends TestCase
{
    #[Test]
    public function itsSchemaRequiresEveryPropertyForOpenAiStrictMode(): void
    {
        $schema = new ObjectSchema(new TripSummaryAgent()->schema(new JsonSchemaTypeFactory()))->toSchema();

        $this->assertEqualsCanonicalizing(array_keys($schema['properties']), $schema['required']);

        $stop = $schema['properties']['stops']['items'];

        $this->assertEqualsCanonicalizing(array_keys($stop['properties']), $stop['required']);
        $this->assertFalse($stop['additionalProperties']);
    }

    #[Test]
    public function itRendersItsInstructions(): void
    {
        $this->assertStringContainsString('gluten free trip planner', (string) new TripSummaryAgent()->instructions());
    }
}
