<?php

namespace App\Services;

/**
 * Клас для завантаження файлів із віддаленого сервера.
 */
class FileDownloader
{
    /**
     * Завантажує файл за вказаною URL-адресою та зберігає його у вказаному місці.
     *
     * @param string $url URL-адреса файлу для завантаження.
     * @param string $destination Локальний шлях для збереження файлу.
     * @return bool Повертає true, якщо файл успішно збережено, інакше false.
     */
    public function download(string $url, string $destination): bool
    {
        echo "\n🔹 Завантаження ZIP-файлу...\n";
        return file_put_contents($destination, file_get_contents($url)) !== false;
    }
}
