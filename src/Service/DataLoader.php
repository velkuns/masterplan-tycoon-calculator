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
use Application\VO\Translations;

class DataLoader
{
    /**
     * @param string $file
     * @return BuildingCollection
     * @throws \JsonException
     */
    public function load(string $file): BuildingCollection
    {
        $data = $this->loadFile($file);

        $collection = new BuildingCollection();

        foreach ($data as $rank => $buildingsForRank) {
            foreach ($buildingsForRank as $name => $buildingData) {
                $building = new Building((int) $rank, $name, $buildingData);
                $collection[$building->getId()] = $building;
            }
        }

        return $collection;
    }
    /**
     * @param string $path
     * @param string[] $langs
     * @return Translations
     * @throws \JsonException
     */
    public function loadTranslations(string $path, array $langs): Translations
    {
        $translations = [];
        foreach ($langs as $lang) {
            $data = $this->loadLang("$path/$lang.json");
            $data = array_merge(...array_values($data));
            foreach ($data as $reference => $name) {
                $translations[$reference][$lang] = $name;
            }
        }

        /** @var array<string, array{en: string, fr: string}> $translations */
        return new Translations($translations);
    }

    /**
     * @return array<string, array<string, array{
     *      cost:        array<string, int>,
     *      recipes?:    list<array{time: int, produce: array<string, int>|null, consume: array<string, int>|null}>,
     *      maintenance: array<string, int>|null,
     *      proximity:   string|null
     * }>> $data
     * @throws \JsonException
     */
    private function loadFile(string $file): array
    {
        $content = (string) file_get_contents($file);

        /**
         * @var array<string, array<string, array{
         *      cost:        array<string, int>,
         *      recipes?:    list<array{time: int, produce: array<string, int>|null, consume: array<string, int>|null}>,
         *      maintenance: array<string, int>|null,
         *      proximity:   string|null
         * }>> $data */
        $data =  json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $data;
    }

    /**
     * @return array{building: array<string, string>, resources: array<string, string>} $data
     * @throws \JsonException
     */
    private function loadLang(string $file): array
    {
        $content = (string) file_get_contents($file);

        /** @var array{building: array<string, string>, resources: array<string, string>} $data */
        $data =  json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $data;
    }
}
