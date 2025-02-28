<?php

namespace App\Repositories;

use PDO;

/**
 * Репозиторій для роботи з таблицею поштових індексів.
 */
class PostIndexRepository
{
    /** @var PDO Об'єкт підключення до бази даних */
    private PDO $pdo;

    /**
     * Конструктор класу.
     *
     * @param PDO $pdo Підключення до бази даних
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Створює тимчасову таблицю для оновлення поштових індексів.
     */
    public function createTempTable(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS post_indexes_tmp");
        $this->pdo->exec("CREATE TEMPORARY TABLE post_indexes_tmp LIKE post_indexes");
    }

    /**
     * Додає дані у тимчасову таблицю.
     *
     * @param array $data Масив значень для вставки
     */
    public function insertIntoTemp(array $data): void
    {
        $sql = "INSERT INTO post_indexes_tmp 
            (oblast, old_district, new_district, settlement, postal_code, region, district_new, settlement_eng, post_branch, post_office, post_code_office, manual_entry)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    /**
     * Оновлює основну таблицю з тимчасової таблиці.
     */
    public function updateMainTable(): void
    {
        $sql = "REPLACE INTO post_indexes 
            (oblast, old_district, new_district, settlement, postal_code, region, district_new, settlement_eng, post_branch, post_office, post_code_office, manual_entry)
            SELECT oblast, old_district, new_district, settlement, postal_code, region, district_new, settlement_eng, post_branch, post_office, post_code_office, manual_entry
            FROM post_indexes_tmp";
        $this->pdo->exec($sql);
    }

    /**
     * Видаляє старі записи, які не присутні у новому завантаженні.
     *
     * @return int Кількість видалених рядків
     */
    public function deleteOldRecords(): int
    {
        $sql = "DELETE FROM post_indexes WHERE manual_entry = 0 
            AND postal_code NOT IN (SELECT postal_code FROM post_indexes_tmp)";
        return $this->pdo->exec($sql);
    }
}
