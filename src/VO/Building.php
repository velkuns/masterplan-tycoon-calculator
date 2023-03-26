<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\VO;

class Building
{
    /** @var Resource[] cost */
    private array $cost = [];

    /** @var Recipe[] recipes */
    private array $recipes = [];

    /** @var Resource[] maintenance */
    private array $maintenance = [];
    private string|null $proximity;
    private int $cycleTime;

    /**
     * @phpstan-param array{
     *      cost:        array<string, int>,
     *      recipes?:    list<array{time: int, produce: array<string, int>|null, consume: array<string, int>|null}>,
     *      maintenance: array<string, int>|null,
     *      proximity:   string|null
     * } $data
     */
    public function __construct(private readonly int $rank, private readonly string $name, array $data)
    {
        foreach ($data['cost'] as $name => $quantity) {
            $this->cost[] = new Resource($name, $quantity);
        }

        foreach ($data['recipes'] ?? [] as $recipe) {
            $this->recipes[] = new Recipe($recipe['time'], $recipe['produce'] ?? [], $recipe['consume'] ?? []);
        }

        foreach ($data['maintenance'] ?? [] as $name => $quantity) {
            $this->maintenance[] = new Resource($name, $quantity);
        }

        $this->proximity = $data['proximity'];

        $this->cycleTime = array_reduce(
            $this->getRecipes(),
            fn(int $time, Recipe $recipe) => $time + $recipe->getTime(),
            0
        );
    }

    public function getId(): string
    {
        return substr(md5($this->name), 0, 10);
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Resource[]
     * @phpstan-return list<Resource>
     */
    public function getCost(): array
    {
        return $this->cost;
    }

    /**
     * @return Recipe[]
     * @phpstan-return list<Recipe>
     */
    public function getRecipes(): array
    {
        return $this->recipes;
    }

    public function getRecipeFor(string $resourceName): Recipe
    {
        foreach ($this->getRecipes() as $recipe) {
            foreach ($recipe->getProduce() as $resource) {
                if ($resource->getName() === $resourceName) {
                    return $recipe;
                }
            }
        }

        throw new \RuntimeException("Invalid data! Building cannot produce {$resourceName} !");
    }

    public function getProducedResourceByName(string $resourceName): Resource
    {
        foreach ($this->getRecipes() as $recipe) {
            foreach ($recipe->getProduce() as $resource) {
                if ($resource->getName() === $resourceName) {
                    return $resource;
                }
            }
        }

        throw new \RuntimeException("Invalid data! Building cannot produce {$resourceName} !");
    }

    public function getCycleTime(): int
    {
        return $this->cycleTime;
    }

    /**
     * @return Resource[]
     */
    public function getMaintenance(): array
    {
        return $this->maintenance;
    }

    /**
     * @return string|null
     */
    public function getProximity(): ?string
    {
        return $this->proximity;
    }
}
