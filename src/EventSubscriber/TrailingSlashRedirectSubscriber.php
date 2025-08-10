<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TrailingSlashRedirectSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 300]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
            $qs = $request->getQueryString();
            $uri = $path;
            if ($qs) {
                $uri .= '?' . $qs;
            }
            $event->setResponse(new RedirectResponse($uri, 301));
        }
    }
}
