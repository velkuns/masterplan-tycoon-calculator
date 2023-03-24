<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\VO;

class Resource
{
    public function __construct(
        private readonly string $name,
        private readonly int $quantity
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getQuantityPerMinute(int $time): float
    {
        return ((60 / $time) * $this->getQuantity());
    }
}
