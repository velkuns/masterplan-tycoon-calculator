<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Service\Twig;

use Symfony\Component\Routing\Router;

class TwigHelper
{
    public function __construct(
        private readonly Router $router
    ) {
    }

    /**
     * @phpstan-return array<string, callable>
     */
    public function getCallbackFunctions(): array
    {
        return [
            'path'     => $this->path(...),
            'image'    => $this->image(...),
        ];
    }

    /**
     * @phpstan-param array<string, int|string|array<int|string>> $params
     */
    public function path(string $routeName, array $params = []): string
    {
        return $this->router->generate($routeName, $params);
    }

    public function image(string $filename, string $baseUrl = '/images'): string
    {
        return rtrim($baseUrl, ' /') . '/' . $filename;
    }
}
