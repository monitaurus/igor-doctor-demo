<?php

/*
 * This file is part of the Leaky Blog demo, based on the Symfony Demo application.
 */

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Attribute\AsTwigFunction;

/**
 * Lets readers see dates in their own timezone: the footer picker sends ?tz=…,
 * which is remembered in a cookie. Templates format dates with display_timezone().
 */
final class DisplayTimezoneSubscriber implements EventSubscriberInterface
{
    public const COOKIE = 'display_tz';
    public const CHOICES = ['UTC', 'Europe/Paris', 'America/New_York', 'Asia/Tokyo', 'Australia/Sydney'];

    public function __construct(private RequestStack $requestStack)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $timezone = $request->query->getString('tz', $request->cookies->getString(self::COOKIE));

        if (\in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            $request->attributes->set(self::COOKIE, $timezone);
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $timezone = $event->getRequest()->query->getString('tz');

        if ($event->isMainRequest() && \in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            $event->getResponse()->headers->setCookie(Cookie::create(self::COOKIE, $timezone, '+1 year'));
        }
    }

    #[AsTwigFunction('display_timezone')]
    public function displayTimezone(): string
    {
        return $this->requestStack->getCurrentRequest()?->attributes->getString(self::COOKIE) ?: date_default_timezone_get();
    }

    /**
     * @return list<string>
     */
    #[AsTwigFunction('display_timezone_choices')]
    public function choices(): array
    {
        return self::CHOICES;
    }
}
