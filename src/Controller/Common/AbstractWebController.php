<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Controller\Common;

use Application\Controller\Common\Traits\AssetsAwareTrait;
use Application\Service\Twig\TwigControllerAwareTrait;
use Eureka\Component\Web\Menu\MenuControllerAwareTrait;
use Eureka\Component\Web\Meta\MetaControllerAwareTrait;
use Eureka\Component\Web\Session\SessionAwareTrait;
use Eureka\Kernel\Http\Controller\Controller;
use Eureka\Kernel\Http\Exception\HttpInternalServerErrorException;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractWebController extends Controller
{
    use AssetsAwareTrait;
    use MenuControllerAwareTrait;
    use MetaControllerAwareTrait;
    use SessionAwareTrait;
    use TwigControllerAwareTrait;

    /**
     * @throws \JsonException
     */
    public function preAction(?ServerRequestInterface $serverRequest = null): void
    {
        if ($serverRequest === null) {
            throw new HttpInternalServerErrorException('Server Request not defined !', 500);
        }
        parent::preAction($serverRequest);

        $this->setServerRequest($serverRequest);
        $this->initializeAssets();

        $currentUri = $serverRequest->getUri()->withPort(null);
        $baseUriImage = $currentUri
            ->withPath('')
            ->withFragment('')
            ->withQuery('')
        ;

        $cookie = $serverRequest->getCookieParams();

        $this->getContext()
            ->add('mainMenu', $this->getMenu())
            ->add('meta', $this->getMeta())
            ->add('flashNotifications', $this->getAllFlashNotification())
            ->add('flashFormErrors', $this->getFormErrors())
            ->add('cssFiles', $this->getCssFiles())
            ->add('jsFiles', $this->getJsFiles())
            ->add('currentUrl', (string) $currentUri)
            ->add('baseUriImage', (string) $baseUriImage)
            ->add('isDarkMode', isset($cookie['dark-mode']) && $cookie['dark-mode'] === 'true')
        ;

        $this->getSession()?->clearFlash();
    }

    protected function getLang(): string
    {
        $cookie = $this->getServerRequest()->getCookieParams();

        return $cookie['lang'] ?? 'en';
    }

    /**
     * @param string $title
     * @return void
     */
    protected function setPageTitle(string $title): void
    {
        $this->getContext()->add('pageTitle', $title . ' - Masterplan Tycoon Calculator');
    }
}
