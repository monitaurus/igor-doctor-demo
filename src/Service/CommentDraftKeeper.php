<?php

/*
 * This file is part of the Leaky Blog demo, based on the Symfony Demo application.
 */

namespace App\Service;

use App\Entity\User;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Keeps a rejected comment while the post page is rendered again, so the comment
 * form (rendered in a sub-request) shows the reader's text and the reason it was rejected.
 */
final class CommentDraftKeeper implements ResetInterface
{
    private ?User $author = null;
    private ?string $draft = null;
    private ?string $error = null;

    public function keep(User $author, string $draft, string $error): void
    {
        $this->author = $author;
        $this->draft = $draft;
        $this->error = $error;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function getDraft(): ?string
    {
        return $this->draft;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function reset(): void
    {
        $this->author = null;
        $this->draft = null;
        $this->error = null;
    }
}
