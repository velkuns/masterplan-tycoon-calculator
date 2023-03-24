<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Tests;

use PHPUnit\Framework\TestCase;

class EmptyTest extends TestCase
{
    /**
     * @return void
     */
    public function testEmpty(): void
    {
        //~ Given
        $bool = true;

        //~ When


        //~ Then
        $this->assertTrue($bool);
    }
}
