<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class HttpCacheSubscriber implements EventSubscriberInterface
{
    public function __construct(private Security $security)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $route = $request->attributes->get('_route');
        if (!\is_string($route) || !str_starts_with($route, 'app_blog_')) {
            return;
        }

        if (null !== $this->security->getUser()) {
            $response->setPrivate();

            return;
        }

        $response->setPublic();
        $response->setSharedMaxAge(600);

        $updatedAt = $request->attributes->get('updated_at');
        if ($updatedAt instanceof \DateTimeInterface) {
            $response->setLastModified($updatedAt);
            $response->setEtag(md5($updatedAt->format(DATE_ATOM).$request->getPathInfo()));

            if ($response->isNotModified($request)) {
                $event->setResponse($response);
            }
        }
    }
}
