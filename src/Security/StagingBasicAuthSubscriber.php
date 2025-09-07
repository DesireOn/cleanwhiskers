<?php

namespace App\Security;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Adds a simple HTTP Basic Auth gate for the staging environment.
 * Enable via env var STAGING_BASIC_AUTH_ENABLED=1 and set user/pass.
 */
final class StagingBasicAuthSubscriber implements EventSubscriberInterface
{
    private KernelInterface $kernel;
    private bool $enabled;
    private string $username;
    private string $password;
    private string $realm;

    public function __construct(
        KernelInterface $kernel,
        bool $enabled = false,
        string $username = '',
        string $password = '',
        string $realm = 'Staging'
    ) {
        $this->kernel = $kernel;
        $this->enabled = $enabled;
        $this->username = $username;
        $this->password = $password;
        $this->realm = $realm;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 512],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->enabled) {
            return;
        }

        if ($this->kernel->getEnvironment() !== 'staging') {
            return;
        }

        // If credentials are not provided, do not block access to avoid lockout
        if ($this->username === '' || $this->password === '') {
            return;
        }

        $request = $event->getRequest();
        $providedUser = (string) $request->getUser();
        $providedPass = (string) $request->getPassword();

        if ($providedUser !== $this->username || $providedPass !== $this->password) {
            $response = new Response('', Response::HTTP_UNAUTHORIZED);
            $response->headers->set('WWW-Authenticate', sprintf('Basic realm="%s", charset="UTF-8"', $this->realm));
            $event->setResponse($response);
        }
    }
}

