<?php

/*
 * This file is part of the Leaky Blog demo, based on the Symfony Demo application.
 */

namespace App\Service;

use Twig\Attribute\AsTwigFunction;

/**
 * Breadcrumb trail shown under the navigation bar. Controllers add the pages
 * below the blog home; the layout renders the trail.
 */
final class Breadcrumbs
{
    /**
     * @var list<array{label: string, url: string|null}>
     */
    private array $items = [];

    public function add(string $label, ?string $url = null): void
    {
        $this->items[] = ['label' => $label, 'url' => $url];
    }

    /**
     * @return list<array{label: string, url: string|null}>
     */
    #[AsTwigFunction('breadcrumbs')]
    public function all(): array
    {
        return $this->items;
    }

    public function clear(): void
    {
        $this->items = [];
    }
}
