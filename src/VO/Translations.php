<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\VO;

class Translations
{
    /**
     * @param array<string, array{en: string, fr: string}> $translations
     */
    public function __construct(
        private readonly array $translations,
    ) {
    }

    public function getTranslation(string $reference, string $lang): string
    {
        return $this->translations[$reference][$lang] ?? $reference;
    }
}
