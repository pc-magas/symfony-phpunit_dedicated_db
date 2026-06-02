<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;

readonly class BaseRepository
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    )
    {
    }
}
