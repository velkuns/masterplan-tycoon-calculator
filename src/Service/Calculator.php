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
use Application\VO\Production;
use Application\VO\Resource;

class Calculator
{
    private const MAINTENANCE_TIME = 180; // Time for maintenance consumption

    /**
     * @param BuildingCollection $buildings
     * @param array<string, float> $buildingWantList
     * @return array<string, array{building: Building, quantity: float, resources: array<string>}>
     */
    public function solve(BuildingCollection $buildings, array $buildingWantList): array
    {
        $production  = new Production();
        $resourceMap = $this->getBuildingsResourceMap($buildings);

        /** @var \SplQueue<array{0: string, 1: float}> $queue */
        $queue = new \SplQueue();

        foreach ($buildingWantList as $id => $nbBuildings) {
            //~ Get building object
            $building = $buildings[$id];

            //~ Add current building maintenance & consume resources requirements to queue
            $this->enqueueBuildingMaintenanceRequirements($queue, $building, $nbBuildings);
            $this->enqueueBuildingConsumeRequirements($queue, $building, $nbBuildings);
        }

        while (!$queue->isEmpty()) {
            $data = $queue->dequeue();

            [$resourceName, $quantityNeeded] = $data;

            //~ Check if resource is already produce in suffisant quantity regarding the desired quantity
            if ($production->produceEnoughResource($resourceName, $quantityNeeded)) {
                $production->add($resourceName, 0, $quantityNeeded, 0); // just add consume part
                continue;
            }

            //~ Get build which produce desired resource
            $building = $resourceMap[$resourceName];

            /** @var Building $building */
            //~ For building, get produced resource, get quantity produced / min & count number of build need to satisfy
            //~ quantity needed (round up to next integer)
            $producedResource = $building->getProducedResourceByName($resourceName);
            $producedPerMin   = $producedResource->getQuantityPerMinute($building->getCycleTime());
            $nbBuildings      = (int) ceil($quantityNeeded / $producedPerMin);

            $production->add($resourceName, $producedPerMin * $nbBuildings, $quantityNeeded, $nbBuildings);

            //~ Add current building maintenance & consume resources requirements to queue
            $this->enqueueBuildingMaintenanceRequirements($queue, $building, $nbBuildings);
            $this->enqueueBuildingConsumeRequirements($queue, $building, $nbBuildings);
        }

        return $production->needBuildings($resourceMap);
    }

    /**
     * @param \SplQueue<array{0: string, 1: float}> $queue
     * @param Building $building
     * @param float $nbBuilding
     * @return void
     */
    private function enqueueBuildingMaintenanceRequirements(
        \SplQueue $queue,
        Building $building,
        float $nbBuilding
    ): void {
        /** @var Resource $resource */
        foreach ($building->getMaintenance() as $resource) {
            //~ Calculate resource quantity needed to satisfy maintenance cost (for the number of building)
            $quantity = $resource->getQuantityPerMinute(self::MAINTENANCE_TIME) * $nbBuilding;
            $queue->enqueue([$resource->getName(), $quantity]);
        }
    }

    /**
     * @param \SplQueue<array{0: string, 1: float}> $queue
     * @param Building $building
     * @param float $nbBuilding
     * @return void
     */
    private function enqueueBuildingConsumeRequirements(
        \SplQueue $queue,
        Building $building,
        float $nbBuilding
    ): void {
        foreach ($building->getRecipes() as $recipe) {
            foreach ($recipe->getConsume() as $resource) {
                //~ Calculate resource quantity consumed (for the number of building) to work at 100%
                $quantity = $resource->getQuantityPerMinute($recipe->getTime()) * $nbBuilding;
                $queue->enqueue([$resource->getName(), $quantity]);
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
