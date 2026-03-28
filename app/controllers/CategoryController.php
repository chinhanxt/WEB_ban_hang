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
        if (wantsJsonResponse()) {
            respondJson([
                'categories' => $categories,
            ]);
        }
        include 'app/views/category/list.php';
    }

    public function add(){
        requireAdmin();
        if (wantsJsonResponse()) {
            respondJson([
                'message' => 'Dữ liệu form thêm danh mục.',
            ]);
        }
        include 'app/views/category/add.php';
    }

    public function save(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            requireAdmin();
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            
            if (empty($name)) {
                if (wantsJsonResponse()) {
                    respondJson([
                        'message' => 'Tên danh mục không được để trống.'
                    ], 422);
                }
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'title' => 'Lỗi!',
                    'text' => 'Tên danh mục không được để trống.'
                ];
                header('Location: /webbanhang/CategoryController/add');
                return;
            }

            $this->categoryModel->addCategory($name, $description);
            if (wantsJsonResponse()) {
                respondJson([
                    'message' => 'Danh mục mới đã được tạo.',
                    'category' => [
                        'name' => $name,
                        'description' => $description,
                    ],
                ], 201);
            }
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
