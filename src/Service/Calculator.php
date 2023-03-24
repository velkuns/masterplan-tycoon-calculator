<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Service;

use Application\Collection\BuildingCollection;
use Application\VO\Building;
use Application\VO\Resource;

class Calculator
{
    private const MAINTENANCE_TIME = 60; // Time for maintenance consumption
    private const MAINTENANCE_TIME_RATIO_PER_MINUTE = self::MAINTENANCE_TIME / 60; // need per minute.

    /**
     * @param BuildingCollection $buildings
     * @param array<string, float> $buildingWantList
     * @return array<string, array{building: Building, quantity: float}>
     */
    public function solve(BuildingCollection $buildings, array $buildingWantList): array
    {
        $resourceMap = $this->getBuildingsResourceMap($buildings);

        /** @var \SplQueue<array{0:Building, 1: Resource, 2: float}> $queue */
        $queue = new \SplQueue();

        foreach ($buildingWantList as $id => $quantity) {
            //~ Get building object
            /** @var Building $building */
            $building = $buildings[$id];

            $this->enqueueBuildingMaintenanceRequirements($queue, $building, $quantity, $resourceMap);
            $this->enqueueBuildingConsumeRequirements($queue, $building, $quantity, $resourceMap);
        }

        $needBuildings = [];
        while (!$queue->isEmpty()) {
            /** @var array{0:Building, 1: Resource, 2: float} $data */
            $data = $queue->dequeue();

            [$building, $resource, $quantity] = $data;

            $buildingQuantity = $this->countNeededBuilding($queue, $building, $resource->getName(), $quantity, $resourceMap);
            $needBuildings[$building->getId()] ??= ['quantity' => 0, 'building' => $building];
            $needBuildings[$building->getId()]['quantity'] += $buildingQuantity;

            //~ Add current building maintenance dependencies resource from building dep to queue
            $this->enqueueBuildingMaintenanceRequirements($queue, $building, $quantity, $resourceMap);
        }

        return $needBuildings;
    }

    /**
     * Count number of dependency building based on ratio / minute that building can produce to have the desired
     * quantity of that resource.
     *
     * @param \SplQueue<array{0:Building, 1: Resource, 2: float}> $queue
     * @param Building $building
     * @param string $resourceName
     * @param float $quantity
     * @param BuildingCollection $resourceMap
     * @return float
     */
    private function countNeededBuilding(
        \SplQueue $queue,
        Building $building,
        string $resourceName,
        float $quantity,
        BuildingCollection $resourceMap
    ): float {
        $needPerMinute = $quantity / self::MAINTENANCE_TIME_RATIO_PER_MINUTE;

        //~ Search for recipe that produce needed $resource
        $recipe   = $building->getRecipeFor($resourceName);

        //~ Then, get that resource and quantity
        $resource = $recipe->getProducedResourceByName($resourceName);

        //~ Get full cycle time for all recipe (some building have many recipes, and game run each sequentially)
        $cycleTime = $building->getCycleTime();

        //~ Then get produced resource needed / minute (based on full cycle & number of quantity needed)
        $producedPerMinute = $resource->getQuantityPerMinute($cycleTime);

        $countNeedBuilding = ($needPerMinute / $producedPerMinute);

        foreach ($recipe->getConsume() as $consumedResource) {
            $depBuilding = $resourceMap[$consumedResource->getName()];
            $depQuantity = $consumedResource->getQuantityPerMinute($cycleTime) * $countNeedBuilding;
            $queue->enqueue([$depBuilding, $consumedResource, $depQuantity]);
        }

        return $countNeedBuilding;
    }

    /**
     * @param \SplQueue<array{0:Building, 1: Resource, 2: float}> $queue
     * @param Building $building
     * @param float $quantity
     * @param BuildingCollection $resourceMap
     * @return void
     */
    private function enqueueBuildingMaintenanceRequirements(
        \SplQueue $queue,
        Building $building,
        float $quantity,
        BuildingCollection $resourceMap
    ): void {
        foreach ($building->getMaintenance() as $maintenanceResource) {
            $depBuilding = $resourceMap[$maintenanceResource->getName()];
            $depQuantity = $maintenanceResource->getQuantityPerMinute(self::MAINTENANCE_TIME) * $quantity;
            $queue->enqueue([$depBuilding, $maintenanceResource, $depQuantity]);
        }
    }

    /**
     * @param \SplQueue<array{0:Building, 1: Resource, 2: float}> $queue
     * @param Building $building
     * @param float $quantity
     * @param BuildingCollection $resourceMap
     * @return void
     */
    private function enqueueBuildingConsumeRequirements(
        \SplQueue $queue,
        Building $building,
        float $quantity,
        BuildingCollection $resourceMap
    ): void {
        foreach ($building->getRecipes() as $recipe) {
            foreach ($recipe->getConsume() as $consumedResource) {
                $depBuilding = $resourceMap[$consumedResource->getName()];
                $depQuantity = $consumedResource->getQuantityPerMinute($recipe->getTime()) * $quantity;
                $queue->enqueue([$depBuilding, $consumedResource, $depQuantity]);
            }
        }
    }

    /**
     * @param BuildingCollection $buildings
     * @return BuildingCollection
     */
    private function getBuildingsResourceMap(BuildingCollection $buildings): BuildingCollection
    {
        $map = new BuildingCollection();

        foreach ($buildings as $building) {
            foreach ($building->getRecipes() as $recipe) {
                /** @var Resource $resource */
                foreach ($recipe->getProduce() as $resource) {
                    $map[$resource->getName()] = $building;
                }
            }
        }

        return $map;
    }
}
