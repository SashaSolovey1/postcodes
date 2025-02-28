<?php

namespace App\Services;

use PDO;

/**
 * Сервіс для роботи з поштовими індексами.
 */
class PostIndexService
{
    /**
     * @var PDO Об'єкт підключення до бази даних.
     */
    private PDO $pdo;

    /**
     * Конструктор класу.
     *
     * @param PDO $pdo Екземпляр PDO для роботи з базою даних.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Отримує список поштових індексів із можливістю фільтрації та пагінації.
     *
     * @param array $queryParams Масив параметрів фільтрації та пагінації.
     *     - postal_code (string) Фільтрація за поштовим індексом.
     *     - settlement (string) Фільтрація за населеним пунктом (пошук за частковим співпадінням).
     *     - limit (int) Кількість записів на сторінці (за замовчуванням 50).
     *     - page (int) Номер сторінки (за замовчуванням 1).
     * @return array Масив із записами поштових індексів та загальною кількістю записів.
     *     - items (array) Масив записів поштових індексів.
     *     - total (int) Загальна кількість записів у базі.
     */
    public function getPostIndexes(array $queryParams): array
    {
        $sql = "SELECT * FROM post_indexes";
        $countSql = "SELECT COUNT(*) as total FROM post_indexes";
        $conditions = [];
        $params = [];

        // Фільтрація за поштовим індексом
        if (!empty($queryParams['postal_code'])) {
            $conditions[] = "postal_code = :postal_code";
            $params[':postal_code'] = $queryParams['postal_code'];
        }

        // Фільтрація за населеним пунктом
        if (!empty($queryParams['settlement'])) {
            $conditions[] = "settlement LIKE :settlement";
            $params[':settlement'] = "%" . $queryParams['settlement'] . "%";
        }

        // Додаємо умови до SQL-запиту
        if (!empty($conditions)) {
            $whereClause = " WHERE " . implode(" AND ", $conditions);
            $sql .= $whereClause;
            $countSql .= $whereClause; // Додаємо фільтри до запиту підрахунку
        }

        // Сортування за замовчуванням: якщо є фільтр по населеному пункту — сортуємо по ньому, інакше по поштовому індексу
        $sql .= " ORDER BY " . (!empty($queryParams['settlement']) ? "settlement" : "postal_code") . " ASC";

        // Пагінація
        $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 50;
        $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
        $offset = ($page - 1) * $limit;

        $sql .= " LIMIT :limit OFFSET :offset";

        // Виконуємо запит підрахунку загальної кількості записів
        $countStmt = $this->pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        // Виконання основного SQL-запиту
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items' => $items,
            'total' => $total
        ];
    }
}
