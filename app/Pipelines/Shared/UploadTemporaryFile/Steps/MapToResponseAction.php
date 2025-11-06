<?php

declare(strict_types=1);

namespace App\Pipelines\Shared\UploadTemporaryFile\Steps;

use App\Contracts\TemporaryFileUploadPipelineActionContract;
use App\DataObjects\TemporaryFileUploadPipelineData;
use App\Models\TemporaryFileUpload;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Storage;

class MapToResponseAction implements TemporaryFileUploadPipelineActionContract
{
    public function handle(TemporaryFileUploadPipelineData $pipelineData, Closure $next): mixed
    {
        /** @var TemporaryFileUpload $model */
        $model = $pipelineData->model;

        $pipelineData->returnData = [
            'id' => $model->id,
            'path' => Storage::disk('uploads')->temporaryUrl((string) $pipelineData->path, Carbon::now()->addMinutes(5)),
        ];

        return $next($pipelineData);
    }
}
