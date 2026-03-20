<?php

$url = $_GET['url'] ?? '';

$url = rtrim($url, '/');
$url = explode('/', $url);

/*
url ví dụ:

/ProductController/edit/5
$url[0] = ProductController
$url[1] = edit
$url[2] = 5
*/

$controllerName = !empty($url[0]) ? $url[0] : 'ProductController';
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