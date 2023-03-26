<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\VO;

use Application\Collection\BuildingCollection;

class Production
{
    /** @var array<string, array{nbBuildings: int, produce: float, consume: float}> $production */
    private array $production = [];

    public function add(string $resourceName, float $produce, float $consume, int $nbBuildings): void
    {
        if (!isset($this->production[$resourceName])) {
            $this->production[$resourceName] = ['nbBuildings' => 0, 'produce' => 0.0, 'consume' => 0.0];
        }

        $this->production[$resourceName]['nbBuildings'] += $nbBuildings;
        $this->production[$resourceName]['produce']     += $produce;
        $this->production[$resourceName]['consume']     += $consume;
    }

    public function produceEnoughResource(string $resourceName, float $quantity): bool
    {
        if (!isset($this->production[$resourceName])) {
            return false;
        }

        $produce = $this->production[$resourceName]['produce'];
        $consume = $this->production[$resourceName]['consume'] + $quantity;
        return (($produce - $consume) > 0);
    }

    /**
     * @param BuildingCollection $resourceMap
     * @return array<string, array{building: Building, quantity: float, resources: array<string>}>
     */
    public function needBuildings(BuildingCollection $resourceMap): array
    {
        $needBuildings = [];
        foreach ($this->production as $resourceName => $data) {
            $overstock = round($data['produce'] - $data['consume'], 1);
            $building = $resourceMap[$resourceName];
            $needBuildings[$building->getId()] ??= ['building' => $building, 'quantity' => 0, 'resources' => []];
            $needBuildings[$building->getId()]['quantity'] += $data['nbBuildings'];
            $needBuildings[$building->getId()]['resources'][] = "{$overstock}x {$resourceName}";
        }

        return $needBuildings;
    }
}
