<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__ . '/src']);
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        PHPUnitSetList::PHPUNIT_110,
    ]);
};
