<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class PreviewNoIndexSubscriber implements EventSubscriberInterface
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        $route = \is_string($route) ? $route : '';
        $path = $request->getPathInfo();

        if ('_preview_error' === $route || str_contains($path, 'preview')) {
            $event->getResponse()->headers->set('X-Robots-Tag', 'noindex');
        }
    }

    /**
     * @return array<class-string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ResponseEvent::class => 'onKernelResponse',
        ];
    }
}
