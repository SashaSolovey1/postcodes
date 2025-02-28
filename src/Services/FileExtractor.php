<?php

namespace App\Services;

use ZipArchive;

/**
 * Клас для розпакування ZIP-архівів.
 */
class FileExtractor
{
    /**
     * Розпаковує ZIP-архів у вказану директорію.
     *
     * @param string $zipPath Шлях до ZIP-архіву.
     * @param string $destination Директорія для розпакування файлів.
     * @return bool Повертає true, якщо архів успішно розпаковано, інакше false.
     */
    public function extract(string $zipPath, string $destination): bool
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) === true) {
            $zip->extractTo($destination);
            $zip->close();
            echo "✅ Архів розпаковано!\n";
            return true;
        }

        echo "❌ Не вдалося розпакувати архів!\n";
        return false;
    }
}
