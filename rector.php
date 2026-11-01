<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector;
use Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/app',
        __DIR__.'/bootstrap/app.php',
        __DIR__.'/tests',
        __DIR__.'/database',
    ]);

    $rectorConfig->sets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
        LevelSetList::UP_TO_PHP_84,
    ]);

    $rectorConfig->skip([
        AddArrowFunctionReturnTypeRector::class,
        ClosureToArrowFunctionRector::class,
        RemoveNonExistingVarAnnotationRector::class,
        RemoveConcatAutocastRector::class,
        RemoveUnusedVariableAssignRector::class,
//        RemoveUnusedPrivateMethodRector::class => [
//            __DIR__.'/app/Http/Integrations/*/Requests/*',
//        ],
//        RemoveUnusedPrivatePropertyRector::class => [
//            __DIR__.'/app/Http/Integrations/*/Requests/*',
//        ],
//        PrivatizeFinalClassMethodRector::class => [
//            __DIR__.'/app/Http/Integrations/*/Requests/*',
//        ],
    ]);

    $rectorConfig->importNames(importDocBlockNames: false);
    $rectorConfig->importShortClasses(false);
};
