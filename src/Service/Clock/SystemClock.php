<?php

/*
 * Copyright (c) Deezer
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Service\Clock;

use Psr\Clock\ClockInterface;

final class SystemClock implements ClockInterface
{
    public const SQL_FORMAT = 'Y-m-d H:i:s';

    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
