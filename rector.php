<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Rector\If_\AbortIfRector;
use RectorLaravel\Rector\If_\ReportIfRector;
use RectorLaravel\Rector\If_\ThrowIfRector;
use RectorLaravel\Rector\MethodCall\AssertStatusToAssertMethodRector;
use RectorLaravel\Rector\MethodCall\AvoidNegatedCollectionFilterOrRejectRector;
use RectorLaravel\Rector\MethodCall\WhereToWhereLikeRector;
use RectorLaravel\Rector\PropertyFetch\ReplaceFakerInstanceWithHelperRector;
use RectorLaravel\Rector\StaticCall\CarbonSetTestNowToTravelToRector;
use RectorLaravel\Rector\StaticCall\EloquentMagicMethodToQueryBuilderRector;
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

    $rectorConfig->rules([
        AvoidNegatedCollectionFilterOrRejectRector::class,
        EloquentMagicMethodToQueryBuilderRector::class,
        AbortIfRector::class,
        ReportIfRector::class,
        ThrowIfRector::class,
        AssertStatusToAssertMethodRector::class,
        CarbonSetTestNowToTravelToRector::class,
        WhereToWhereLikeRector::class,
    ]);

    $rectorConfig->skip([
        ReplaceFakerInstanceWithHelperRector::class,
    ]);

    $rectorConfig->importNames(importDocBlockNames: false);
    $rectorConfig->importShortClasses(false);
};
