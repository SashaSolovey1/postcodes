<?php

/**
 * Файл маршрутизації для API поштових індексів.
 *
 * Реалізує такі маршрути:
 * - GET /api/post-indexes — Отримати список поштових індексів.
 * - GET /api/post-indexes/{post_code} — Отримати конкретний поштовий індекс.
 * - POST /api/post-indexes — Додати новий поштовий індекс.
 * - DELETE /api/post-indexes/{post_code} — Видалити поштовий індекс.
 */

use App\Controllers\PostIndexController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use OpenApi\Attributes as OA;

require __DIR__ . '/../db.php';

// Створюємо об'єкт контролера для роботи з поштовими індексами
$postIndexController = new PostIndexController($pdo);

/**
 * Головна сторінка API.
 *
 * @OA\Get(
 *     path="/",
 *     summary="Головна сторінка API",
 *     @OA\Response(
 *         response=200,
 *         description="Повертає інформацію про доступні маршрути"
 *     )
 * )
 */
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode([
        'message' => 'Welcome to the Postcodes API',
        'routes' => [
            '/api/post-indexes' => 'Get post indexes',
            '/api/post-indexes/{post_code}' => 'Get specific post index',
            '/api/post-indexes (POST)' => 'Add post index',
            '/api/post-indexes/{post_code} (DELETE)' => 'Delete post index'
        ]
    ], JSON_PRETTY_PRINT));

    return $response->withHeader('Content-Type', 'application/json');
});

/**
 * Група маршрутів для роботи з поштовими індексами.
 */
$app->group('/api/post-indexes', function (RouteCollectorProxy $group) use ($postIndexController) {
    /**
     * Отримати список поштових індексів.
     *
     * @OA\Get(
     *     path="/api/post-indexes",
     *     summary="Отримати список поштових індексів",
     *     @OA\Response(
     *         response=200,
     *         description="Успішне отримання списку"
     *     )
     * )
     */
    $group->get('', [$postIndexController, 'getPostIndexes']);

    /**
     * Додати новий поштовий індекс.
     *
     * @OA\Post(
     *     path="/api/post-indexes",
     *     summary="Додати поштовий індекс",
     *     @OA\Response(
     *         response=201,
     *         description="Індекс успішно додано"
     *     )
     * )
     */
    $group->post('', [$postIndexController, 'addPostIndex']);

    /**
     * Видалити поштовий індекс.
     *
     * @OA\Delete(
     *     path="/api/post-indexes/{postal_code}",
     *     summary="Видалити поштовий індекс",
     *     @OA\Parameter(
     *         name="postal_code",
     *         in="path",
     *         required=true,
     *         description="Поштовий індекс для видалення"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Індекс успішно видалено"
     *     )
     * )
     */
    $group->delete('/{postal_code}', [$postIndexController, 'deletePostIndex']);
});
