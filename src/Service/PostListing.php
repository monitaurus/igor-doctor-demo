<?php

/*
 * This file is part of the Leaky Blog demo, based on the Symfony Demo application.
 */

namespace App\Service;

use App\Pagination\Paginator;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Display options of the post lists.
 */
final class PostListing implements ResetInterface
{
    public const PER_PAGE_CHOICES = [Paginator::PAGE_SIZE, 30];

    private int $postsPerPage = Paginator::PAGE_SIZE;

    public function getPostsPerPage(): int
    {
        return $this->postsPerPage;
    }

    public function setPostsPerPage(int $postsPerPage): void
    {
        $this->postsPerPage = $postsPerPage;
    }

    public function reset(): void
    {
        $this->postsPerPage = Paginator::PAGE_SIZE;
    }

    /**
     * Runs $callback with another page size, then restores the reader's choice.
     *
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function withPostsPerPage(int $postsPerPage, callable $callback): mixed
    {
        $previous = $this->postsPerPage;
        $this->postsPerPage = $postsPerPage;

        $result = $callback();

        $this->postsPerPage = $previous;

        return $result;
    }
}
