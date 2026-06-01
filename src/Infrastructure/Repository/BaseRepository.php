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

    /**
     * Disable the softdeleteable filter
     */
    public function disableSoftDeleteFilter(): void
    {
        if ($this->entityManager->getFilters()->isEnabled('softdeleteable')) {
            $this->entityManager->getFilters()->disable('softdeleteable');
        }
    }

    /**
     * Enable the softdeleteable filter
     */
    public function enableSoftDeleteFilter(): void
    {
        if (!$this->entityManager->getFilters()->isEnabled('softdeleteable')) {
            $this->entityManager->getFilters()->enable('softdeleteable');
        }
    }
    /**
     * Run a callback with softdeleteable disabled, then restore the state
     *
     * @template T
     * @param callable():T $callback
     * @return T
     */
    public function withSoftDeleteDisabled(callable $callback): T
    {
        $wasEnabled = $this->entityManager->getFilters()->isEnabled('softdeleteable');

        if ($wasEnabled) {
            $this->entityManager->getFilters()->disable('softdeleteable');
        }

        try {
            return $callback();
        } finally {
            if ($wasEnabled) {
                $this->entityManager->getFilters()->enable('softdeleteable');
            }
        }
    }
}