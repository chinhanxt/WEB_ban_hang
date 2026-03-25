<?php
require_once 'app/config/auth.php';

$url = $_GET['url'] ?? '';

$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

if (($url[0] ?? '') === 'api') {
    $resource = $url[1] ?? '';
    $id = $url[2] ?? null;
    $controllerName = ucfirst($resource) . 'ApiController';
    $controllerFile = 'app/controllers/' . $controllerName . '.php';

    if (!$resource || !file_exists($controllerFile)) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        echo json_encode(['message' => 'API controller not found'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    require_once $controllerFile;
    $controller = new $controllerName();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    switch ($method) {
        case 'GET':
            $action = $id ? 'show' : 'index';
            break;
        case 'POST':
            $action = 'store';
            break;
        case 'PUT':
            $action = $id ? 'update' : '';
            break;
        case 'DELETE':
            $action = $id ? 'destroy' : '';
            break;
        default:
            $action = '';
            break;
    }

    if ($action === '' || !method_exists($controller, $action)) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($id !== null) {
        call_user_func_array([$controller, $action], [$id]);
    } else {
        call_user_func_array([$controller, $action], []);
    }
    exit;
}

/*
url ví dụ:

/ProductController/edit/5
$url[0] = ProductController
$url[1] = edit
$url[2] = 5
*/

$controllerName = !empty($url[0]) ? $url[0] : 'HomeController';
$action = !empty($url[1]) ? $url[1] : 'index';

/* kiểm tra controller tồn tại */
$controllerFile = 'app/controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    die('Controller not found: ' . $controllerFile);
}

/* load controller */
require_once $controllerFile;

/* tạo object controller */
$controller = new $controllerName();

/* kiểm tra action */
if (!method_exists($controller, $action)) {
    die('Action not found: ' . $action);
}

/* gọi action + truyền tham số */
$params = array_slice($url, 2);

call_user_func_array([$controller, $action], $params);
