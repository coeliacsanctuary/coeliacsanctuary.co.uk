<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Rector\Class_\AddExtendsAnnotationToModelFactoriesRector;
use RectorLaravel\Rector\Class_\ModelCastsPropertyToCastsMethodRector;
use RectorLaravel\Rector\PropertyFetch\ReplaceFakerInstanceWithHelperRector;
use RectorLaravel\Rector\PropertyFetch\ReplaceFakerPropertyFetchWithMethodCallRector;
use RectorLaravel\Set\LaravelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap/app.php',
        __DIR__ . '/tests',
        __DIR__ . '/database',
    ]);

    $rectorConfig->sets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
        LevelSetList::UP_TO_PHP_84,
        LaravelSetList::COMPOSER_BASED,
    ]);

    $rectorConfig->skip([
        ReplaceFakerInstanceWithHelperRector::class,
        ReplaceFakerPropertyFetchWithMethodCallRector::class, // do seperate
        AddExtendsAnnotationToModelFactoriesRector::class, // also do on its onw
        ModelCastsPropertyToCastsMethodRector::class, // do again
    ]);

    $rectorConfig->importNames(importDocBlockNames: false);
    $rectorConfig->importShortClasses(false);
};
