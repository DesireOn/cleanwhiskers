<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds HTTP Basic Auth gate for the staging environment host.
 */
final class StagingBasicAuthSubscriber implements EventSubscriberInterface
{
    private const STAGING_HOST = 'staging.cleanwhiskers.com';

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1024],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Only enforce on the staging host
        if ($request->getHost() !== self::STAGING_HOST) {
            return;
        }

        // Read credentials from environment
        $user = $_ENV['STAGING_BASIC_AUTH_USER'] ?? $_SERVER['STAGING_BASIC_AUTH_USER'] ?? null;
        $pass = $_ENV['STAGING_BASIC_AUTH_PASS'] ?? $_SERVER['STAGING_BASIC_AUTH_PASS'] ?? null;

        // If not configured, do not block access to avoid lockout.
        if (!\is_string($user) || $user === '' || !\is_string($pass) || $pass === '') {
            return;
        }

        // Allow health checks without auth if needed
        $path = $request->getPathInfo();
        if ($path === '/healthz' || $path === '/ping') {
            return;
        }

        $providedUser = $request->getUser();
        $providedPass = $request->getPassword();

        if ($providedUser !== $user || $providedPass !== $pass) {
            $response = new Response('Authentication Required', Response::HTTP_UNAUTHORIZED);
            $response->headers->set('WWW-Authenticate', 'Basic realm="Staging"');
            $event->setResponse($response);
        }
    }
}

