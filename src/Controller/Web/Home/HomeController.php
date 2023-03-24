<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Controller\Web\Home;

use Application\Collection\BuildingCollection;
use Application\Controller\Common\AbstractWebController;
use Application\Service\Calculator;
use Application\Service\DataLoader;
use Application\VO\Building;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController extends AbstractWebController
{
    public function __construct(
        private readonly DataLoader $dataLoader,
        private readonly Calculator $calculator
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function view(): ResponseInterface
    {
        $buildings = $this->dataLoader->load((string) \realpath(__DIR__ . '/../../../../data/data.json'));

        $this->getContext()
            ->add('buildings', $this->formatCollection($buildings))
            ->add('lang', $this->getLang())
        ;

        return $this->getResponse($this->render('@app/Home/Home.twig'));
    }

    /**
     * @throws \JsonException
     */
    public function calculate(ServerRequestInterface $serverRequest): ResponseInterface
    {
        $buildings = $this->dataLoader->load((string) \realpath(__DIR__ . '/../../../../data/data.json'));

        $wantToBuild = $this->getBuildingList($serverRequest);
        $needToBuild = $this->calculator->solve($buildings, $wantToBuild);

        $this->getContext()
            ->add('buildings', $this->formatCollection($buildings))
            ->add('wantToBuild', $this->formatWantList($buildings, $wantToBuild))
            ->add('needToBuild', $needToBuild)
            ->add('lang', $this->getLang())
        ;

        return $this->getResponse($this->render('@app/Home/Home.twig'));
    }

    /**
     * @phpstan-return array<string, float>
     */
    private function getBuildingList(ServerRequestInterface $serverRequest): array
    {
        $wantToBuild = [];

        $parsedBody = $serverRequest->getParsedBody();
        $list       = $parsedBody['buildings-list'] ?? [];

        foreach ($list as $idAndQuantity) {
            if (!preg_match('`^(?<id>.+?) x(?<quantity>\d+)$`', $idAndQuantity, $matches)) {
                continue;
            }

            $wantToBuild[$matches['id']] = (float) $matches['quantity'];
        }

        return $wantToBuild;
    }

    /**
     * @param BuildingCollection $collection
     * @return Building[][]
     */
    private function formatCollection(BuildingCollection $collection): array
    {
        $list = [];
        foreach ($collection as $building) {
            $list[$building->getRank()][$building->getId()] = $building;
        }

        return $list;
    }

    /**
     * @param BuildingCollection $buildings
     * @param array<string, float> $wantList
     * @return array<string, array{building: Building, quantity: float}>
     */
    private function formatWantList(BuildingCollection $buildings, array $wantList): array
    {
        $wantToList = [];
        foreach ($wantList as $id => $quantity) {
            $building = $buildings[$id];
            $wantToList[$id] = ['building' => $building, 'quantity' => $quantity];
        }

        return $wantToList;
    }
}
