
# Postcodes API

## Опис

Цей проєкт призначений для автоматичного завантаження, розпакування та імпорту індексів від Укрпошти в базу даних. 
Скрипт працює на PHP (Slim 4) та використовує MySQL для збереження даних. Усі компоненти розгортаються в Docker-контейнерах. 
Frontend працює на Vue.js. 

## Залежності

Для роботи проєкту необхідно мати встановлені:
- [Docker](https://docs.docker.com/get-docker/)
- [npm](https://www.npmjs.com/)

## Встановлення

### 1. Клонування репозиторію
```sh
  git clone https://github.com/SashaSolovey1/postcodes.git
  cd postcodes
```

### 2. Налаштування змінних оточення
```sh
  cp .env.example .env
```

### 3. Запуск контейнерів
```sh
  make up
```

### 4. Імпорт бази даних (тільки при першому запуску)
```sh
  make db
```

### 5. Доступ до бекенду та фронтенду
Після запуску контейнерів сервіси будуть доступні за наступними адресами:

- **Бекенд API (Slim 4):** [http://localhost:8001/](http://localhost:8001/)
- **Фронтенд (Vue.js):** [http://localhost:5173/](http://localhost:5173/)
- **Swagger UI:** [http://localhost:8081/](http://localhost:8081/)


## Використання

### 1. Запуск імпорту вручну
```sh
  make import
```

### 2. Перевірка логів cron
```sh
  make cron
```

### 3. Автоматичний імпорт через cron
Імпорт поштових індексів виконується автоматично кожного дня о **00:00**. Cron-завдання запускається у Docker-контейнері.

## Структура API для індексів

API надає доступ до даних поштових індексів. Нижче наведено основні ендпоінти:

### 🔹 Отримати всі поштові індекси
```
GET /api/post-indexes
```
**Параметри запиту:**
- `postal_code` (string) - фільтр за поштовим індексом
- `settlement` (string) - фільтр за населеним пунктом
- `page` (integer) - номер сторінки (пагінація)
- `limit` (integer) - кількість записів на сторінку

**Відповідь (200 OK):**
```json
[
  {
    "id": 1,
    "oblast": "Київська",
    "old_district": "Києво-Святошинський",
    "new_district": "Бучанський",
    "settlement": "Боярка",
    "postal_code": "08150",
    "region": "Центральний",
    "district_new": "Бучанський",
    "settlement_eng": "Boiarka",
    "post_branch": "Відділення №1",
    "post_office": "Укрпошта",
    "post_code_office": "08150"
  }
]
```

### 🔹 Додати новий поштовий індекс
```
POST /api/post-indexes
```
**Тіло запиту (JSON):**
```json
{
  "oblast": "Вінницька",
  "old_district": "Вінницький",
  "new_district": "Вінницький",
  "settlement": "Вінниця",
  "postal_code": "21004",
  "region": "Центральний",
  "district_new": "Вінницький район",
  "settlement_eng": "Vinnytsia",
  "post_branch": "Відділення №4",
  "post_office": "Відділення №4",
  "post_code_office": "21004-4"
}
```
**Відповіді:**
- `201 Created` - Успішно додано
- `400 Bad Request` - Невірний запит
- `500 Internal Server Error` - Помилка сервера

### 🔹 Отримати індекс за кодом
```
GET /api/post-indexes/{postal_code}
```
**Приклад:**
```
GET /api/post-indexes/08150
```
**Відповідь (200 OK):**
```json
{
  "id": 1,
  "oblast": "Київська",
  "old_district": "Києво-Святошинський",
  "new_district": "Бучанський",
  "settlement": "Боярка",
  "postal_code": "08150",
  "region": "Центральний",
  "district_new": "Бучанський",
  "settlement_eng": "Boiarka",
  "post_branch": "Відділення №1",
  "post_office": "Укрпошта",
  "post_code_office": "08150"
}
```

### 🔹 Видалити поштовий індекс
```
DELETE /api/post-indexes/{postal_code}
```
**Приклад:**
```
DELETE /api/post-indexes/08150
```
**Відповіді:**
- `200 OK` - Успішно видалено
- `404 Not Found` - Поштовий індекс не знайдено
- `500 Internal Server Error` - Помилка сервера

## Технології
- PHP 8.2
- Slim 4
- Vue.js
- MySQL
- Docker

## Посилання
- [Slim Framework](https://www.slimframework.com/)
- [Docker](https://www.docker.com/)