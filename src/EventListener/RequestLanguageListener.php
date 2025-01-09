<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'processLanguage', priority: 101)]
final class RequestLanguageListener
{
    /**
     * Sets the request locale from language headers
     * - `Content-Language` for write requests
     * - `Accept-Language` for read requests
     */
    public function processLanguage(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $contentLanguage = $request->headers->get('Content-Language');
        if ($contentLanguage !== null) {
            $request->setLocale($contentLanguage);
        }

        $acceptLanguage = $request->headers->get('Accept-Language');
        if ($acceptLanguage !== null) {
            $request->setLocale($acceptLanguage);
        }
    }
}
