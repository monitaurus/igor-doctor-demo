<?php

/*
 * This file is part of the Leaky Blog demo, based on the Symfony Demo application.
 */

namespace App\Twig;

use Twig\Attribute\AsTwigFunction;

/**
 * Timezone in which dates are displayed (see DisplayTimezoneSubscriber).
 */
final class TimezoneExtension
{
    public const CHOICES = ['UTC', 'Europe/Paris', 'America/New_York', 'Asia/Tokyo', 'Australia/Sydney'];

    #[AsTwigFunction('display_timezone')]
    public function displayTimezone(): string
    {
        return date_default_timezone_get();
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
