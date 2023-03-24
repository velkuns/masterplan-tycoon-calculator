<?php

/*
 * Copyright (c) Deezer
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Collection;

/**
 * @template TKey
 * @template TValue
 * @extends \Iterator<TKey, TValue>
 * @extends \ArrayAccess<TKey, TValue>
 */
interface CollectionInterface extends \Iterator, \ArrayAccess, \Countable
{
}
