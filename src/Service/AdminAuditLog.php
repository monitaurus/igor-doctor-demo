<?php

/*
 * This file is part of the Leaky Blog demo, based on the Symfony Demo application.
 */

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Records what administrators do with posts. Entries are buffered and written to
 * the audit file in one go, when the request is over.
 */
final class AdminAuditLog implements ResetInterface
{
    /**
     * @var list<string>
     */
    private array $buffer = [];

    public function __construct(
        #[Autowire('%kernel.logs_dir%/admin-audit.log')]
        private readonly string $file,
    ) {
    }

    public function record(User $admin, string $action, Post $post): void
    {
        $this->buffer[] = \sprintf(
            "%s\t%s\t%s\t%s\n",
            new \DateTimeImmutable('now', new \DateTimeZone('UTC'))->format(\DATE_ATOM),
            $admin->getUserIdentifier(),
            $action,
            $post->getTitle(),
        );
    }

    /**
     * @return list<array{date: string, admin: string, action: string, post: string}> most recent first
     */
    public function entries(): array
    {
        if (!is_file($this->file)) {
            return [];
        }

        $entries = [];
        foreach (file($this->file, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            [$date, $admin, $action, $post] = array_pad(explode("\t", $line, 4), 4, '');
            $entries[] = ['date' => $date, 'admin' => $admin, 'action' => $action, 'post' => $post];
        }

        return array_reverse($entries);
    }

    public function reset(): void
    {
        if ([] !== $this->buffer) {
            file_put_contents($this->file, implode('', $this->buffer), \FILE_APPEND | \LOCK_EX);
        }

        $this->buffer = [];
    }
}
