<?php

namespace App;

use App\Repositories\PostIndexRepository;
use App\Services\ExcelParser;
use App\Services\FileDownloader;
use App\Services\FileExtractor;

/**
 * Клас для імпорту поштових індексів.
 */
class PostIndexImporter
{
    private FileDownloader $downloader;
    private FileExtractor $extractor;
    private ExcelParser $parser;
    private PostIndexRepository $repository;
    private string $zipPath;
    private string $filePath;
    private string $zipUrl;

    /**
     * Конструктор класу.
     *
     * @param FileDownloader $downloader Сервіс для завантаження файлу
     * @param FileExtractor $extractor Сервіс для розпакування архіву
     * @param ExcelParser $parser Сервіс для парсингу Excel-файлу
     * @param PostIndexRepository $repository Репозиторій для роботи з базою даних
     * @param string $zipUrl URL-адреса архіву з індексами
     */
    public function __construct(
        FileDownloader $downloader,
        FileExtractor $extractor,
        ExcelParser $parser,
        PostIndexRepository $repository,
        string $zipUrl
    ) {
        $this->downloader = $downloader;
        $this->extractor = $extractor;
        $this->parser = $parser;
        $this->repository = $repository;
        $this->zipPath = __DIR__ . "/postindex.zip";
        $this->filePath = __DIR__ . "/postindex.xlsx";
        $this->zipUrl = $zipUrl;
    }

    /**
     * Основний метод запуску імпорту поштових індексів.
     *
     * @return void
     */
    public function run(): void
    {
        // Завантаження ZIP-файлу
        if (!$this->downloader->download($this->zipUrl, $this->zipPath)) {
            die("❌ Помилка завантаження ZIP!\n");
        }

        // Розпаковка ZIP-файлу
        if (!$this->extractor->extract($this->zipPath, __DIR__)) {
            die("❌ Помилка розпаковки ZIP!\n");
        }

        // Парсинг XLSX-файлу
        $rows = $this->parser->parse($this->filePath);
        $startRow = $this->findStartRow($rows);

        // Оновлення бази даних
        $this->repository->createTempTable();
        $this->importData(array_slice($rows, $startRow));
        $this->repository->updateMainTable();
        $deleted = $this->repository->deleteOldRecords();

        echo "🗑 Видалено $deleted застарілих записів!\n";

        // Видалення тимчасових файлів
        unlink($this->zipPath);
        unlink($this->filePath);
        echo "🔹 Тимчасові файли видалено.\n";
    }

    /**
     * Знаходить перший рядок з даними у файлі.
     *
     * @param array $rows Масив рядків із XLSX
     * @return int Індекс першого рядка з даними
     */
    private function findStartRow(array $rows): int
    {
        foreach ($rows as $index => $row) {
            if (!empty(array_filter($row))) {
                return $index + 1;
            }
        }
        return 0;
    }

    /**
     * Імпортує дані у базу.
     *
     * @param array $rows Масив рядків із XLSX
     * @return void
     */
    private function importData(array $rows): void
    {
        $uniqueRows = [];
        $totalCount = 0;

        foreach ($rows as $row) {
            // Пропускаємо порожні або неповні рядки
            if (count(array_filter($row)) < 5 || empty($row[11])) continue;

            // Видаляємо пусті перші стовпці, якщо такі є
            while (empty($row[0]) && count($row) > 1) {
                array_shift($row);
            }

            // Обрізаємо масив до 11 колонок
            $row = array_slice($row, 0, 11);
            $postalCode = $row[4] ?? null;

            // Пропускаємо дублікати за поштовим індексом
            if ($postalCode === null || isset($uniqueRows[$postalCode])) continue;

            $uniqueRows[$postalCode] = true;
            $this->repository->insertIntoTemp($row);

            // Виводимо повідомлення кожні 1000 оброблених записів
            if (++$totalCount % 1000 === 0) {
                echo "✅ Оброблено $totalCount унікальних записів...\n";
            }
        }
        echo "✅ Всього оброблено: $totalCount записів!\n";
    }
}
