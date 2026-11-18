<?php

/*
 * This file is part of the Leaky Blog demo, based on the Symfony Demo application.
 */

namespace App\EventListener;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Keeps the search index in sync: the posts created or changed by a flush are
 * collected in onFlush and re-indexed once the transaction is committed.
 */
#[AsDoctrineListener(event: Events::onFlush)]
#[AsDoctrineListener(event: Events::postFlush)]
final class SearchIndexListener implements ResetInterface
{
    /**
     * @var array<string, string> post slug => post title
     */
    private array $postsToIndex = [];

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $unitOfWork = $args->getObjectManager()->getUnitOfWork();

        foreach ([...$unitOfWork->getScheduledEntityInsertions(), ...$unitOfWork->getScheduledEntityUpdates()] as $entity) {
            if ($entity instanceof Post) {
                $this->postsToIndex[(string) $entity->getSlug()] = (string) $entity->getTitle();
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if ([] === $this->postsToIndex) {
            return;
        }

        // A real application would push the documents to its search engine here.
        $this->logger->notice('Search index updated for {count} post(s): {posts}.', [
            'count' => \count($this->postsToIndex),
            'posts' => implode(', ', array_keys($this->postsToIndex)),
        ]);

        $this->postsToIndex = [];
    }

    public function reset(): void
    {
        $this->postsToIndex = [];
    }
}
