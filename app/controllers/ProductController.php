<?php

require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/models/OrderModel.php';

class ProductController
{

    private $productModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
    }

    public function index()
    {
        $limit = 8;
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        $category_id = $_GET['category_id'] ?? null;
        $min_price = $_GET['min_price'] ?? null;
        $max_price = $_GET['max_price'] ?? null;

        $products = $this->productModel->getFilteredProducts($limit, $offset, $category_id, $min_price, $max_price);
        $totalProducts = $this->productModel->countFilteredProducts($category_id, $min_price, $max_price);
        $totalPages = ceil($totalProducts / $limit);

        $categories = (new CategoryModel($this->db))->getCategories();

        include 'app/views/product/list.php';
    }

    public function add()
    {
        $categories = (new CategoryModel($this->db))->getCategories();
        include 'app/views/product/add.php';
    }

    public function edit($id)
    {
        $product = $this->productModel->getProductById($id);

        if (!$product) {
            die("Product not found");
        }

        $categories = (new CategoryModel($this->db))->getCategories();

        include 'app/views/product/edit.php';

    }
    // public function edit($id)
    // {
    //     $product = $this->productModel->getProductById($id);

    //     var_dump($product);
    //     exit;
    // }
    public function update()
    {
        session_start();
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = $_POST['price'];
        $category_id = $_POST['category_id'];

        $errors = [];
        if (strlen($name) < 10) $errors[] = "Tên sản phẩm phải có ít nhất 10 ký tự.";
        if (!is_numeric($price) || $price <= 0) $errors[] = "Giá sản phẩm phải là số dương.";
        if (empty($description)) $errors[] = "Mô tả sản phẩm không được để trống.";

        if (!empty($errors)) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Lỗi dữ liệu!',
                'text' => implode('<br>', $errors)
            ];
            header("Location: /webbanhang/ProductController/edit/" . $id);
            return;
        }

        $image = $_POST['old_image'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'title' => 'Ảnh không hợp lệ!',
                    'text' => 'Chỉ cho phép định dạng: jpg, jpeg, png, gif.'
                ];
                header("Location: /webbanhang/ProductController/edit/" . $id);
                return;
            }

            $target = "uploads/" . time() . "_" . $filename;
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
            $image = $target;
        }

        $this->productModel->updateProduct($id, $name, $description, $price, $category_id, $image);
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'title' => 'Cập nhật thành công!',
            'text' => 'Thông tin sản phẩm đã được lưu lại.'
        ];
        header("Location: /webbanhang/ProductController");
    }

    public function delete($id)
    {
        session_start();
        if ($this->productModel->isProductInOrder($id)) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Lỗi ràng buộc!',
                'text' => 'Không thể xóa! Sản phẩm này đang có trong các đơn hàng hiện tại.'
            ];
            header("Location: /webbanhang/ProductController");
            return;
        }
        
        $this->productModel->deleteProduct($id);
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'title' => 'Thành công!',
            'text' => 'Sản phẩm đã được xóa khỏi hệ thống.'
        ];
        header("Location: /webbanhang/ProductController");
    }


    public function save()
    {
        session_start();
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = $_POST['price'];
        $category_id = $_POST['category_id'];

        $errors = [];
        if (strlen($name) < 10) {
            $errors[] = "Tên sản phẩm phải có ít nhất 10 ký tự.";
        }
        if (!is_numeric($price) || $price <= 0) {
            $errors[] = "Giá sản phẩm phải là số dương.";
        }
        if (empty($description)) {
            $errors[] = "Mô tả sản phẩm không được để trống.";
        }

        if (!empty($errors)) {
            $categories = (new CategoryModel($this->db))->getCategories();
            include 'app/views/product/add.php';
            return;
        }

        $image = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $target = "uploads/" . time() . "_" . $filename;
                move_uploaded_file($_FILES['image']['tmp_name'], $target);
                $image = $target;
            }
        }

        $this->productModel->addProduct($name, $description, $price, $category_id, $image);
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'title' => 'Hoàn tất!',
            'text' => 'Sản phẩm mới đã được thêm thành công.'
        ];
        header("Location: /webbanhang/ProductController");
    }

    public function search()
    {
        $keyword = $_GET['keyword'] ?? '';

        $products = $this->productModel->search($keyword);

        $totalPages = 1;

        include 'app/views/product/list.php';
    }

    public function show($id)
    {
        $product = $this->productModel->getProductById($id);

        if (!$product) {
            die("Không tìm thấy sản phẩm");
        }

        include 'app/views/product/show.php';
    }

    public function addToCart($id)
    {
        session_start();

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]++;
        } else {
            $_SESSION['cart'][$id] = 1;
        }

        header("Location: /webbanhang/ProductController/cart");
    }

    public function cart()
    {
        session_start();

        $cart = $_SESSION['cart'] ?? [];

        $products = [];

        foreach ($cart as $id => $qty) {
            $product = $this->productModel->getProductById($id);
            if ($product) {
                $product->qty = $qty;
                $products[] = $product;
            } else {
                // Nếu sản phẩm không còn tồn tại trong DB, tự động xóa khỏi giỏ hàng
                unset($_SESSION['cart'][$id]);
            }
        }

        include 'app/views/product/cart.php';
    }

    public function removeFromCart($id)
    {
        session_start();

        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }

        header("Location: /webbanhang/ProductController/cart");
    }

    public function checkout($id = null)
    {
        session_start();

        $selected_ids = $_POST['selected_ids'] ?? null;

        if ($id) {
            // Thanh toán riêng lẻ 1 sản phẩm (từ nút "Mua ngay")
            $product = $this->productModel->getProductById($id);
            if (!$product) {
                die("Sản phẩm không tồn tại");
            }
            $qty = (isset($_SESSION['cart'][$id])) ? $_SESSION['cart'][$id] : 1;
            $product->qty = $qty;
            $products = [$product];
            $single_product_id = $id;
        } elseif ($selected_ids) {
            // Thanh toán các sản phẩm được chọn từ giỏ hàng
            $products = [];
            foreach ($selected_ids as $pid) {
                $product = $this->productModel->getProductById($pid);
                if ($product) {
                    $product->qty = $_SESSION['cart'][$pid] ?? 1;
                    $products[] = $product;
                }
            }
        } else {
            // Thanh toán toàn bộ giỏ hàng (nếu truy cập trực tiếp URL)
            if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
                echo "<script>
                    alert('Giỏ hàng trống! Bạn cần thêm sản phẩm trước khi thanh toán.');
                    window.location='/webbanhang/ProductController';
                  </script>";
                exit;
            }

            $cart = $_SESSION['cart'];
            $products = [];

            foreach ($cart as $pid => $qty) {
                $product = $this->productModel->getProductById($pid);
                if ($product) {
                    $product->qty = $qty;
                    $products[] = $product;
                }
            }
        }

        if (empty($products)) {
            header("Location: /webbanhang/ProductController/cart");
            exit;
        }

        include 'app/views/product/checkout.php';
    }

    public function placeOrder()
    {
        session_start();
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address']);
        $payment_method = $_POST['payment_method'] ?? 'Tiền mặt';
        $notes = trim($_POST['notes'] ?? '');
        
        $single_id = $_POST['single_product_id'] ?? null;
        $selected_ids = $_POST['selected_ids'] ?? null; // Cần thêm input hidden này vào view checkout

        $errors = [];
        if (empty($name)) $errors[] = "Vui lòng nhập họ tên.";
        if (empty($phone) || !preg_match('/^[0-9]{10,11}$/', $phone)) $errors[] = "Số điện thoại không hợp lệ (10-11 chữ số).";
        if (empty($address)) $errors[] = "Vui lòng nhập địa chỉ giao hàng.";

        if (!empty($errors)) {
            // Logic load lại products để hiện lỗi (tương tự checkout)
            // ... (giữ đơn giản để tập trung vào tính năng chọn hàng)
            include 'app/views/product/checkout.php';
            return;
        }

        $orderModel = new OrderModel($this->db);
        $total = 0;
        $order_items = [];

        if ($single_id) {
            $product = $this->productModel->getProductById($single_id);
            $qty = (isset($_SESSION['cart'][$single_id])) ? $_SESSION['cart'][$single_id] : 1;
            $total = $product->Price * $qty;
            $order_items[] = ['id' => $single_id, 'qty' => $qty, 'price' => $product->Price];
            unset($_SESSION['cart'][$single_id]);
        } elseif ($selected_ids) {
            foreach ($selected_ids as $pid) {
                if (isset($_SESSION['cart'][$pid])) {
                    $product = $this->productModel->getProductById($pid);
                    $qty = $_SESSION['cart'][$pid];
                    $total += $product->Price * $qty;
                    $order_items[] = ['id' => $pid, 'qty' => $qty, 'price' => $product->Price];
                    unset($_SESSION['cart'][$pid]);
                }
            }
        } else {
            foreach ($_SESSION['cart'] as $pid => $qty) {
                $product = $this->productModel->getProductById($pid);
                $total += $product->Price * $qty;
                $order_items[] = ['id' => $pid, 'qty' => $qty, 'price' => $product->Price];
            }
            unset($_SESSION['cart']);
        }

        $order_id = $orderModel->createOrder($total, $name, $address, $phone, $email, $payment_method, $notes);
        foreach ($order_items as $item) {
            $orderModel->addOrderDetail($order_id, $item['id'], $item['qty'], $item['price']);
        }

        $_SESSION['flash_message'] = [
            'type' => 'success',
            'title' => 'Đặt hàng thành công!',
            'text' => 'Cảm ơn bạn đã mua hàng. Đơn hàng của bạn đang được xử lý.'
        ];
        header("Location: /webbanhang/OrderController");
    }

    public function addToCartAjax($id)
    {
        session_start();
        $status = 'success';

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]++;
            $status = 'exists';
        } else {
            $_SESSION['cart'][$id] = 1;
        }

        $count = count($_SESSION['cart']);

        echo json_encode([
            "count" => $count,
            "status" => $status
        ]);
    }

    public function increaseCart($id)
    {
        session_start();

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]++;
        }

        header("Location: /webbanhang/ProductController/cart");
    }

    public function decreaseCart($id)
    {
        session_start();

        if (isset($_SESSION['cart'][$id])) {

            $_SESSION['cart'][$id]--;

            if ($_SESSION['cart'][$id] <= 0) {
                unset($_SESSION['cart'][$id]);
            }

        }

        header("Location: /webbanhang/ProductController/cart");
    }

    public function updateCartAjax()
    {
        session_start();

        $id = $_POST['id'] ?? null;
        $qty = (int)($_POST['qty'] ?? 1);

        if ($id) {
            if ($qty < 1) {
                $qty = 1; // Luôn giữ tối thiểu là 1 thay vì xóa sản phẩm khi nhập 0
            }
            $_SESSION['cart'][$id] = $qty;
        }

        $total = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $pid => $q) {
                $product = $this->productModel->getProductById($pid);
                if ($product) {
                    $total += $product->Price * $q;
                }
            }
        }

        // Đếm số loại sản phẩm (Shopee style)
        $count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

        echo json_encode([
            "total" => number_format($total) . " VND",
            "count" => $count
        ]);
    }

    public function buyNow($id)
    {
        header("Location: /webbanhang/ProductController/checkout/$id");
        exit;
    }
}

