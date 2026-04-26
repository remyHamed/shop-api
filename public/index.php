<?php

use DI\ContainerBuilder;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use App\Http\Controller\StoreController;
use App\Http\Controller\AuthController;
use App\Http\Controller\UserController;
use App\Http\Controller\ProductController;
use App\Http\Middleware\AuthMiddleware;
use App\Security\JwtHandler;
use App\Repository\Interfaces\StoreRepositoryInterface;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Repository\Interfaces\ProductRepositoryInterface;
use App\Repository\Interfaces\SaleRepositoryInterface;
use App\Repository\Persistence\SqlStoreRepository;
use App\Repository\Persistence\SqlUserRepository;
use App\Repository\Persistence\SqlProductRepository;
use App\Repository\Persistence\SqlSaleRepository;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    PDO::class => function() {
        return new PDO(
            "mysql:host=db;dbname=store_db;charset=utf8mb4", 
            "root", 
            "root", 
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    },
    JwtHandler::class => \DI\create(),

    StoreRepositoryInterface::class   => \DI\autowire(SqlStoreRepository::class),
    UserRepositoryInterface::class    => \DI\autowire(SqlUserRepository::class),
    ProductRepositoryInterface::class => \DI\autowire(SqlProductRepository::class),
    SaleRepositoryInterface::class    => \DI\autowire(SqlSaleRepository::class),
]);
$container = $containerBuilder->build();

$dispatcher = simpleDispatcher(function(RouteCollector $r) {
    $r->addGroup('/api', function (RouteCollector $r) {
        $r->addRoute('POST', '/login', [AuthController::class, 'login']);
        $r->addRoute('POST', '/register', [UserController::class, 'register']);
        $r->addRoute('POST', '/users/employee', [UserController::class, 'createEmployee']);
        $r->addRoute('GET',  '/stores', [StoreController::class, 'list']);
        $r->addRoute('POST', '/stores', [StoreController::class, 'create']);
        $r->addRoute('GET',  '/stores/{storeId:\d+}/products', [ProductController::class, 'listByStore']);
        $r->addRoute('POST', '/products', [ProductController::class, 'create']);
        $r->addRoute('POST', '/products/{productId:\d+}/sell', [ProductController::class, 'sell']);
        $r->addRoute('PUT', '/api/products/{id:\d+}', [ProductController::class, 'update']);
        $r->addRoute('DELETE', '/api/products/{id:\d+}', [ProductController::class, 'delete']);
    });
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case \FastRoute\Dispatcher::NOT_FOUND:
        sendJsonResponse(['error' => 'Route non trouvée'], 404);
        break;

    case \FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1]; 
        $vars = $routeInfo[2];   

        try {
            $auth = $container->get(AuthMiddleware::class);

            if ($handler[0] === StoreController::class && $httpMethod === 'POST') {
                $auth->check('ROLE_EMPLOYEE'); 
            }

            if ($handler[0] === UserController::class && $handler[1] === 'createEmployee') {
                $auth->check('ROLE_ADMIN'); 
            }

            if ($handler[0] === ProductController::class && $httpMethod === 'POST') {
                $auth->check('ROLE_EMPLOYEE');
            }

            $controller = $container->get($handler[0]);
            $method = $handler[1];
            $response = $controller->$method($vars);

            sendJsonResponse($response);

        } catch (\Exception $e) {
            $code = ($e instanceof \InvalidArgumentException) ? 400 : 500;
            sendJsonResponse(['error' => $e->getMessage()], $code);
        }
        break;
}

function sendJsonResponse(array $data, int $code = 200): void {
    header("Content-Type: application/json", true, $code);
    echo json_encode($data);
    exit;
}