<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * This custom Doctrine repository is empty because so far we don't need any custom
 * method to query for application user information. But it's always a good practice
 * to define a custom repository that will be used when the application grows.
 *
 * See https://symfony.com/doc/current/doctrine.html#querying-for-objects-the-repository
 *
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 *
 * @template-extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * @return array<string, int> tag name => number of posts, most used first
     */
    public function countPostsByTag(): array
    {
        /** @var list<array{name: string, posts: int|string}> $rows */
        $rows = $this->createQueryBuilder('t')
            ->select('t.name AS name', 'COUNT(p.id) AS posts')
            ->innerJoin('App\\Entity\\Post', 'p', 'WITH', 't MEMBER OF p.tags')
            ->groupBy('t.id')
            ->orderBy('posts', 'DESC')
            ->addOrderBy('t.name', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_combine(array_column($rows, 'name'), array_map('intval', array_column($rows, 'posts')));
    }
}
