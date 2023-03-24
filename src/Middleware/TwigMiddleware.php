<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Middleware;

use Application\Service\DataLoader;
use Application\Service\Twig\TwigHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig;

class TwigMiddleware implements MiddlewareInterface
{
    /**
     * TwigMiddleware constructor.
     *
     * @param Twig\Environment $twig
     * @param TwigHelper $twigHelper
     * @param array<string, string> $twigPaths
     */
    public function __construct(
        private readonly Twig\Environment $twig,
        private readonly TwigHelper $twigHelper,
        private readonly DataLoader $dataLoader,
        private readonly array $twigPaths = []
    ) {
    }

    /**
     * @throws Twig\Error\LoaderError
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->configurePaths($this->twigPaths);
        $this->configureFunctions();
        $this->configureExtensions();
        $this->configureFilters();

        return $handler->handle($request);
    }

    /**
     * @phpstan-param array<string, string> $paths
     * @throws Twig\Error\LoaderError
     */
    private function configurePaths(array $paths): void
    {
        //~ Add path
        $loader = $this->twig->getLoader();
        if ($loader instanceof Twig\Loader\FilesystemLoader) {
            foreach ($paths as $path => $namespace) {
                $loader->addPath($path, $namespace);
            }
        }
    }

    private function configureFunctions(): void
    {
        //~ Add functions to main twig instance
        foreach ($this->twigHelper->getCallbackFunctions() as $name => $callback) {
            $this->twig->addFunction(new Twig\TwigFunction($name, $callback));
        }
    }

    private function configureExtensions(): void
    {
        // Nothing for now
    }

    /**
     * @throws \JsonException
     */
    private function configureFilters(): void
    {
        $path = (string) \realpath(__DIR__ . '/../../data/');
        $translation = $this->dataLoader->loadTranslations($path, ['en', 'fr']);
        $this->twig->addFilter(new Twig\TwigFilter('time', fn(int $seconds) => date('i:s', $seconds)));
        $this->twig->addFilter(new Twig\TwigFilter('translate', $translation->getTranslation(...)));
    }
}
