<?php

/*
 * This file is part of the Leaky Blog demo, based on the Symfony Demo application.
 */

namespace App\Service;

use App\Repository\TagRepository;

/**
 * Builds the tag cloud of the blog sidebar: every tag with the number of posts using it.
 */
final class TagCloud
{
    /**
     * Computed once: the query joins every post.
     *
     * @var array<string, int>|null tag name => number of posts
     */
    private static ?array $cloud = null;

    public function __construct(
        private readonly TagRepository $tags,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function get(): array
    {
        if (null === self::$cloud) {
            self::$cloud = $this->tags->countPostsByTag();
        }

        return self::$cloud;
    }
}
