<?php

namespace App\Services;

use App\Repositories\PostIndexRepository;
use PDO;

/**
 * Сервіс для роботи з поштовими індексами.
 */
class PostIndexService
{
    private PDO $pdo;
    private PostIndexRepository $repository;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->repository = new PostIndexRepository($this->pdo);
    }

    /**
     * Отримує список поштових індексів із можливістю фільтрації та пагінації.
     *
     * @param array $queryParams Масив параметрів фільтрації та пагінації.
     * @return array Масив із записами поштових індексів та загальною кількістю записів.
     */
    public function getPostIndexes(array $queryParams): array
    {
        return $this->repository->getFilteredPostIndexes($queryParams);
    }

}
