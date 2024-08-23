<?php

declare(strict_types=1);

namespace App\Jobs\OpenGraphImages;

use App\Actions\OpenGraphImages\GenerateCountyOpenGraphImageAction;
use App\Actions\OpenGraphImages\GenerateEateryOpenGraphImageAction;
use App\Actions\OpenGraphImages\GenerateNationwideBranchOpenGraphImageAction;
use App\Actions\OpenGraphImages\GenerateTownOpenGraphImageAction;
use App\Contracts\HasOpenGraphImageContract;
use App\Contracts\OpenGraphActionContract;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Models\OpenGraphImage;
use App\Services\RenderOpenGraphImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class CreateEatingOutOpenGraphImageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public function __construct(public HasOpenGraphImageContract $model)
    {
        //
    }

    public function handle(RenderOpenGraphImage $renderOpenGraphImage): void
    {
        if (config('coeliac.generate_og_images') === false) {
            return;
        }

        /** @var OpenGraphActionContract $action */
        $action = match ($this->model::class) {
            EateryCounty::class => app(GenerateCountyOpenGraphImageAction::class),
            EateryTown::class => app(GenerateTownOpenGraphImageAction::class),
            Eatery::class => app(GenerateEateryOpenGraphImageAction::class),
            NationwideBranch::class => app(GenerateNationwideBranchOpenGraphImageAction::class),
            default => throw new RuntimeException('Unknown class'),
        };

        $base64Image = $renderOpenGraphImage->handle($action->handle($this->model)->render());

        /** @var OpenGraphImage $openGraphModel */
        $openGraphModel = $this->model->openGraphImage()->firstOrCreate();

        $openGraphModel->clearMediaCollection();
        $openGraphModel->addMediaFromBase64($base64Image)->usingFileName('og-image.png')->toMediaCollection();
    }
}
