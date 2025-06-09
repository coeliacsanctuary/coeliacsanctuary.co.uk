<?php

declare(strict_types=1);

namespace Tests\Code;

use App\Actions\Shop\ShipOrderAction;
use App\Nova\Resource;
use App\Nova\Resources\EatingOut\PolymorphicPanels\EateryFeaturesPolymorphicPanel;
use Jpeters8889\ApexCharts\Chartable;
use Jpeters8889\PhpUnitCodeAssertions\CodeAssertionsTestCase;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Dashboard;
use PHPUnit\Framework\Attributes\Test;

class NovaArchitectureTest extends CodeAssertionsTestCase
{
    #[Test]
    public function allNovaActionsFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Nova/Actions')
            ->areClasses()
            ->extends(Action::class)->except(ShipOrderAction::class);
    }

    #[Test]
    public function allNovaChartablesFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Nova/Chartables')
            ->areClasses()
            ->extends(Chartable::class);
    }

    #[Test]
    public function allNovaDashboardsFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Nova/Dashboards')
            ->areClasses()
            ->extends(Dashboard::class);
    }

    #[Test]
    public function allNovaResourceExtendTheBaseResource(): void
    {
        $this->assertClassesIn('app/Nova/Resources')
            ->areClasses()
            ->extend(Resource::class)->except(EateryFeaturesPolymorphicPanel::class);
    }
}
