<?php

/**
 * Імпорт поштових індексів з Excel-файлу.
 *
 * Скрипт завантажує ZIP-архів, розпаковує його, парсить Excel-файл
 * та оновлює базу даних з новими поштовими індексами.
 */

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../db.php';

use App\PostIndexImporter;
use App\Services\FileDownloader;
use App\Services\FileExtractor;
use App\Services\ExcelParser;
use App\Repositories\PostIndexRepository;

/**
 * Створюємо екземпляр імпортера та запускаємо процес імпорту.
 */
$importer = new PostIndexImporter(
    new FileDownloader(),
    new FileExtractor(),
    new ExcelParser(),
    new PostIndexRepository($pdo),
    $_ENV['ZIP_URL']
);

$importer->run();
