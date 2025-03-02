<?php

namespace App\Repositories;

use InvalidArgumentException;
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

    /**
     * Додавання поштових індексів (одного або декількох)
     *
     * @param array $records Масив записів, що містять поштові індекси для додавання.
     * @return int Кількість успішно доданих записів.
     * @throws InvalidArgumentException Якщо відсутні обов'язкові поля у записі.
     */
    public function addPostIndexes(array $records): int
    {
        $requiredFields = ['oblast', 'settlement', 'postal_code', 'region', 'post_branch', 'post_office', 'post_code_office'];
        $optionalFields = ['old_district', 'new_district', 'district_new', 'settlement_eng'];

        $checkSql = "SELECT COUNT(*) FROM post_indexes WHERE postal_code = :postal_code";
        $insertSql = "INSERT INTO post_indexes 
                      (oblast, old_district, new_district, settlement, postal_code, region, district_new, settlement_eng, post_branch, post_office, post_code_office, manual_entry) 
                      VALUES (:oblast, :old_district, :new_district, :settlement, :postal_code, :region, :district_new, :settlement_eng, :post_branch, :post_office, :post_code_office, 1)";

        $checkStmt = $this->pdo->prepare($checkSql);
        $insertStmt = $this->pdo->prepare($insertSql);

        $inserted = 0;

        foreach ($records as $record) {
            foreach ($requiredFields as $field) {
                if (empty($record[$field])) {
                    throw new InvalidArgumentException("Поле '$field' є обов'язковим.");
                }
            }

            foreach ($optionalFields as $field) {
                if (!isset($record[$field])) {
                    $record[$field] = null;
                }
            }

            $checkStmt->bindValue(':postal_code', $record['postal_code'], PDO::PARAM_STR);
            $checkStmt->execute();
            $exists = $checkStmt->fetchColumn();

            if ($exists) {
                continue;
            }

            foreach ($record as $key => $value) {
                $insertStmt->bindValue(':' . $key, $value, $value !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            }

            if ($insertStmt->execute()) {
                $inserted++;
            }
        }

        return $inserted;
    }

    /**
     * Видалення поштового індексу
     *
     * @param string $postalCode Поштовий індекс, який потрібно видалити.
     * @return bool Повертає true, якщо запис був успішно видалений, і false, якщо запису не існувало.
     */

    public function deletePostIndex(string $postalCode): bool
    {
        $sql = "DELETE FROM post_indexes WHERE postal_code = :postal_code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':postal_code', $postalCode, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
