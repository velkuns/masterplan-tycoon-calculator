<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\VO;

class Recipe
{
    /** @var Resource[] $produce */
    private array $produce = [];

    /** @var Resource[] $consume */
    private array $consume = [];

    /**
     * @phpstan-param array<string, int> $produce
     * @phpstan-param array<string, int> $consume
     */
    public function __construct(
        private readonly int $time,
        array $produce,
        array $consume
    ) {
        foreach ($produce as $name => $quantity) {
            $this->produce[] = new Resource($name, $quantity);
        }

        foreach ($consume as $name => $quantity) {
            $this->consume[] = new Resource($name, $quantity);
        }
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function getTimeRatio(): float
    {
        return 1 / ($this->getTime() / 60);
    }

    /**
     * @return Resource[]
     */
    public function getProduce(): array
    {
        return $this->produce;
    }

    /**
     * @return Resource[]
     */
    public function getConsume(): array
    {
        return $this->consume;
    }

    public function getProducedResourceByName(string $resourceName): Resource
    {
        foreach ($this->getProduce() as $resource) {
            if ($resource->getName() === $resourceName) {
                return $resource;
            }
        }

        throw new \RuntimeException("Recipe does not produce resource {$resourceName}!");
    }
}
