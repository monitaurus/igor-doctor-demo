<?php

/*
 * This file is part of the Leaky Blog demo, based on the Symfony Demo application.
 */

namespace App\Service;

use App\Entity\Post;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Remembers the last posts the reader opened, for the "Recently viewed" sidebar.
 */
final class RecentlyViewedPosts implements ResetInterface
{
    private const MAX = 5;

    /**
     * @var array<string, string> post slug => post title, most recent first
     */
    private array $posts = [];

    public function add(Post $post): void
    {
        $slug = (string) $post->getSlug();

        unset($this->posts[$slug]);
        $this->posts = [$slug => (string) $post->getTitle()] + $this->posts;
        $this->posts = \array_slice($this->posts, 0, self::MAX, true);
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->posts;
    }

    public function reset(): void
    {
        $this->posts = [];
    }
}
