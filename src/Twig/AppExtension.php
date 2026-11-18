<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig;

use App\Entity\Post;
use Symfony\Component\Intl\Locales;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;

/**
 * See https://symfony.com/doc/current/templating/twig_extension.html.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Julien ITARD <julienitard@gmail.com>
 */
final class AppExtension
{
    /**
     * @var list<array{code: string, name: string}>|null
     */
    private ?array $locales = null;

    // The $locales argument is injected thanks to the service container.
    // See https://symfony.com/doc/current/service_container.html#binding-arguments-by-name-or-type
    public function __construct(
        /** @var string[] */
        private readonly array $enabledLocales,
        private readonly string $defaultLocale,
    ) {
    }

    /**
     * Takes the list of codes of the locales (languages) enabled in the
     * application and returns an array with the name of each locale written
     * in its own language (e.g. English, Français, Español, etc.).
     *
     * @return array<int, array<string, string>>
     */
    #[AsTwigFunction('locales')]
    public function getLocales(): array
    {
        if (null !== $this->locales) {
            return $this->locales;
        }

        $this->locales = [];

        foreach ($this->enabledLocales as $localeCode) {
            $this->locales[] = ['code' => $localeCode, 'name' => Locales::getName($localeCode, $localeCode)];
        }

        return $this->locales;
    }

    /**
     * Estimated reading time of a post, in minutes (200 words per minute).
     * Memoized per post: the filter is used on every teaser of the blog index.
     */
    #[AsTwigFilter('reading_time')]
    public function readingTime(Post $post): int
    {
        static $minutes = [];

        return $minutes[$post->getId()] ??= max(1, (int) ceil(str_word_count(strip_tags((string) $post->getContent())) / 200));
    }

    /**
     * Check if the given locale is RTL.
     */
    #[AsTwigFunction('is_rtl')]
    public function isRtl(?string $locale = null): bool
    {
        $locale ??= $this->defaultLocale;

        return \in_array($locale, ['ar', 'fa', 'he', 'ur', 'ps', 'sd'], true);
    }
}
