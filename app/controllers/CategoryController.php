<?php
require_once 'app/models/CategoryModel.php';
require_once 'app/config/database.php';

class CategoryController
{
    private $categoryModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
    }

    public function list(){
        $categories = $this->categoryModel->getCategories();
        include 'app/views/category/list.php';
    }

    public function add(){
        include 'app/views/category/add.php';
    }

    public function save(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            session_start();
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            
            if (empty($name)) {
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'title' => 'Lỗi!',
                    'text' => 'Tên danh mục không được để trống.'
                ];
                header('Location: /webbanhang/CategoryController/add');
                return;
            }

            $this->categoryModel->addCategory($name, $description);
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Thành công!',
                'text' => 'Danh mục mới đã được tạo.'
            ];
            header('Location: /webbanhang/ProductController/add');
        }
    }
}   
?>