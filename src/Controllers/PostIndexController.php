<?php

namespace App\Controllers;

use App\Repositories\PostIndexRepository;
use App\Services\PostIndexService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Postcodes API",
    version: "1.0.0",
    description: "API для роботи з поштовими індексами",
    contact: new OA\Contact(
        name: "Solovei Oleksandr",
        email: "solo160103@gmail.com"
    )
)]
#[OA\Server(
    url: "http://localhost:8001",
    description: "Local development server"
)]


class PostIndexController
{
    private PDO $pdo;
    private PostIndexRepository $repository;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->repository = new PostIndexRepository($this->pdo);
    }

    /**
     * Отримання поштових індексів
     *
     * @param Request $request HTTP-запит
     * @param Response $response HTTP-відповідь
     * @return Response Відповідь з поштовими індексами
     */

    #[OA\Get(
        path: "/api/post-indexes",
        summary: "Отримати всі поштові індекси",
        description: "Повертає список поштових індексів з підтримкою фільтрації, сортування та пагінації.",
        tags: ["Поштові індекси"],
        parameters: [
            new OA\Parameter(name: "postal_code", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "settlement", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "limit", in: "query", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Список поштових індексів", content: new OA\JsonContent())
        ]
    )]
    public function getPostIndexes(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $postIndexService = new PostIndexService($this->pdo);

        $data = $postIndexService->getPostIndexes($queryParams);

        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }


    /**
     * Додавання поштових індексів (одного або декількох)
     *
     * @param Request $request HTTP-запит з JSON-тілом
     * @param Response $response HTTP-відповідь
     * @return Response Відповідь із результатом операції
     */

    #[OA\Post(
        path: "/api/post-indexes",
        summary: "Додати один або кілька поштових індексів",
        description: "Додає один або декілька записів поштового індексу в базу даних.",
        tags: ["Поштові індекси"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "array",
                items: new OA\Items(
                    required: ["oblast", "settlement", "postal_code", "region", "post_branch", "post_office", "post_code_office"],
                    properties: [
                        new OA\Property(property: "oblast", type: "string", example: "Вінницька"),
                        new OA\Property(property: "old_district", type: "string", example: "Вінницький"),
                        new OA\Property(property: "new_district", type: "string", example: "Вінницький"),
                        new OA\Property(property: "settlement", type: "string", example: "Вінниця"),
                        new OA\Property(property: "postal_code", type: "string", example: "21004"),
                        new OA\Property(property: "region", type: "string", example: "Центральний"),
                        new OA\Property(property: "district_new", type: "string", example: "Вінницький район"),
                        new OA\Property(property: "settlement_eng", type: "string", example: "Vinnytsia"),
                        new OA\Property(property: "post_branch", type: "string", example: "Відділення №4"),
                        new OA\Property(property: "post_office", type: "string", example: "Відділення №4"),
                        new OA\Property(property: "post_code_office", type: "string", example: "21004-4")
                    ]
                ),
                example: [
                    [
                        "oblast" => "Вінницька",
                        "old_district" => "Вінницький",
                        "new_district" => "Вінницький",
                        "settlement" => "Вінниця",
                        "postal_code" => "21004",
                        "region" => "Центральний",
                        "district_new" => "Вінницький район",
                        "settlement_eng" => "Vinnytsia",
                        "post_branch" => "Відділення №4",
                        "post_office" => "Відділення №4",
                        "post_code_office" => "21004-4"
                    ],
                    [
                        "oblast" => "Київська",
                        "old_district" => "Києво-Святошинський",
                        "new_district" => "Бучанський",
                        "settlement" => "Буча",
                        "postal_code" => "08292",
                        "region" => "Північний",
                        "district_new" => "Бучанський район",
                        "settlement_eng" => "Bucha",
                        "post_branch" => "Відділення №1",
                        "post_office" => "Відділення №1",
                        "post_code_office" => "08292-1"
                    ]
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Успішно додано"),
            new OA\Response(response: 400, description: "Невірний запит"),
            new OA\Response(response: 409, description: "Помилка: поштовий індекс вже існує"),
            new OA\Response(response: 500, description: "Помилка сервера")
        ]
    )]


    public function addPostIndex(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody(), true);

        if (isset($data[0]) && is_array($data[0])) {
            $records = $data;
        } else {
            $records = [$data];
        }

        try {
            $inserted = $this->repository->addPostIndexes($records);

            if ($inserted === 0) {
                $response->getBody()->write(json_encode(['message' => "Жоден запис не було додано, всі поштові індекси вже існують."]));
                return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
            }

            $response->getBody()->write(json_encode(['message' => "$inserted поштових індексів успішно додано."]));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => "Помилка сервера: " . $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    /**
     * Видалення поштового індексу
     *
     * @param Request $request HTTP-запит
     * @param Response $response HTTP-відповідь
     * @param array $args Аргументи маршруту (postal_code)
     * @return Response Відповідь із результатом операції
     */

    #[OA\Delete(
        path: "/api/post-indexes/{postal_code}",
        summary: "Видалити поштовий індекс",
        description: "Видаляє запис поштового індексу за вказаним `postal_code`.",
        tags: ["Поштові індекси"],
        parameters: [
            new OA\Parameter(
                name: "postal_code",
                in: "path",
                required: true,
                description: "Поштовий індекс",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Успішно видалено"),
            new OA\Response(response: 404, description: "Поштовий індекс не знайдено"),
            new OA\Response(response: 500, description: "Помилка сервера")
        ]
    )]
    public function deletePostIndex(Request $request, Response $response, array $args): Response
    {
        $postalCode = $args['postal_code'];

        if ($this->repository->deletePostIndex($postalCode)) {
            $response->getBody()->write(json_encode(['message' => 'Поштовий індекс успішно видалено']));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['error' => 'Поштовий індекс не знайдено']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }
}
