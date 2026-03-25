<?php

require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';

class HomeController
{
    private $db;
    private $productModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
    }

    public function index()
    {
        $products = $this->productModel->getProducts();
        $featuredProducts = array_slice($products, 0, 8);
        $categories = (new CategoryModel($this->db))->getCategories();
        include 'app/views/home/index.php';
    }
}
