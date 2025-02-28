<?php

namespace App\Services;

use Shuchkin\SimpleXLSX;

/**
 * Сервіс для парсингу Excel-файлів у форматі XLSX.
 */
class ExcelParser
{
    /**
     * Зчитує дані з XLSX-файлу та повертає їх у вигляді масиву.
     *
     * @param string $filePath Шлях до файлу XLSX.
     * @return array Двовимірний масив з даними з таблиці.
     * @throws \Exception Якщо виникла помилка при читанні файлу.
     */
    public function parse(string $filePath): array
    {
        if (!$xlsx = SimpleXLSX::parse($filePath)) {
            throw new \Exception("❌ Помилка читання файлу: " . SimpleXLSX::parseError());
        }
        return $xlsx->rows();
    }
}
