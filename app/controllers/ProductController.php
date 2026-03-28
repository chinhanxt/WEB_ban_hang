<?php

require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/CartModel.php';

class ProductController
{

    private $productModel;
    private $cartModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->cartModel = new CartModel($this->db);
        $this->syncSessionCartIfNeeded();
    }

    private function getCurrentUserId()
    {
        return isset($_SESSION['user']->id) ? (int)$_SESSION['user']->id : null;
    }

    private function syncSessionCartIfNeeded()
    {
        $accountId = $this->getCurrentUserId();

        if (!$accountId || empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            return;
        }

        if ($this->cartModel->syncSessionCart($accountId, $_SESSION['cart'])) {
            unset($_SESSION['cart']);
        }
    }

    private function getCartMap()
    {
        $accountId = $this->getCurrentUserId();
        if ($accountId) {
            return $this->cartModel->getCartQuantitiesByAccountId($accountId);
        }

        return isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
    }

    private function getCartProducts()
    {
        $accountId = $this->getCurrentUserId();
        if ($accountId) {
            return $this->cartModel->getCartProductsByAccountId($accountId);
        }

        $products = [];
        $cart = $this->getCartMap();

        foreach ($cart as $id => $qty) {
            $product = $this->productModel->getProductById($id);
            if ($product) {
                $product->qty = $qty;
                $products[] = $product;
            } else {
                unset($_SESSION['cart'][$id]);
            }
        }

        return $products;
    }

    private function getCartCount()
    {
        $accountId = $this->getCurrentUserId();
        if ($accountId) {
            return $this->cartModel->countItemsByAccountId($accountId);
        }

        return count($this->getCartMap());
    }

    private function getRequestData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') === false) {
            return $_POST;
        }

        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
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

        if (wantsJsonResponse()) {
            respondJson([
                'products' => $products,
                'categories' => $categories,
                'pagination' => [
                    'page' => (int)$page,
                    'limit' => (int)$limit,
                    'total_products' => (int)$totalProducts,
                    'total_pages' => (int)$totalPages,
                ],
            ]);
        }

        include 'app/views/product/list.php';
    }

    public function add()
    {
        requireAdmin();
        $categories = (new CategoryModel($this->db))->getCategories();
        if (wantsJsonResponse()) {
            respondJson([
                'message' => 'Dữ liệu form thêm sản phẩm.',
                'categories' => $categories,
            ]);
        }
        include 'app/views/product/add.php';
        unset($_SESSION['old_input']);
    }

    public function edit($id)
    {
        requireAdmin();
        $product = $this->productModel->getProductById($id);

        if (!$product) {
            die("Product not found");
        }

        $categories = (new CategoryModel($this->db))->getCategories();

        if (wantsJsonResponse()) {
            respondJson([
                'product' => $product,
                'categories' => $categories,
            ]);
        }

        include 'app/views/product/edit.php';

    }
    // public function edit($id)
    // {
    //     $product = $this->productModel->getProductById($id);

    //     var_dump($product);
    //     exit;
    // }
    public function update($id = null)
    {
        requireAdmin();
        $data = $this->getRequestData();
        $id = (int)($id ?? $data['id'] ?? 0);
        $name = trim((string)($data['name'] ?? ''));
        $description = trim((string)($data['description'] ?? ''));
        $price = $data['price'] ?? null;
        $category_id = $data['category_id'] ?? null;

        $errors = [];
        if ($id <= 0) $errors[] = "ID sản phẩm không hợp lệ.";
        if (strlen($name) < 10) $errors[] = "Tên sản phẩm phải có ít nhất 10 ký tự.";
        if (!is_numeric($price) || $price <= 0) $errors[] = "Giá sản phẩm phải là số dương.";
        if (empty($description)) $errors[] = "Mô tả sản phẩm không được để trống.";

        if (!empty($errors)) {
            if (wantsJsonResponse()) {
                respondJson([
                    'message' => 'Dữ liệu cập nhật không hợp lệ.',
                    'errors' => $errors,
                ], 422);
            }
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Lỗi dữ liệu!',
                'text' => implode('<br>', $errors)
            ];
            if ($id > 0) {
                header("Location: /webbanhang/ProductController/edit/" . $id);
            } else {
                header("Location: /webbanhang/ProductController");
            }
            return;
        }

        $image = (string)($data['old_image'] ?? '');

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                if (wantsJsonResponse()) {
                    respondJson([
                        'message' => 'Ảnh không hợp lệ. Chỉ cho phép định dạng: jpg, jpeg, png, gif.',
                    ], 422);
                }
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
        $updatedProduct = $this->productModel->getProductById($id);

        if (wantsJsonResponse()) {
            respondJson([
                'message' => 'Cập nhật sản phẩm thành công.',
                'product' => $updatedProduct,
            ]);
        }
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'title' => 'Cập nhật thành công!',
            'text' => 'Thông tin sản phẩm đã được lưu lại.'
        ];
        header("Location: /webbanhang/ProductController");
    }

    public function delete($id)
    {
        requireAdmin();
        if ($this->productModel->isProductInOrder($id)) {
            if (wantsJsonResponse()) {
                respondJson([
                    'message' => 'Không thể xóa! Sản phẩm này đang có trong các đơn hàng hiện tại.'
                ], 409);
            }
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Lỗi ràng buộc!',
                'text' => 'Không thể xóa! Sản phẩm này đang có trong các đơn hàng hiện tại.'
            ];
            header("Location: /webbanhang/ProductController");
            return;
        }
        
        $this->productModel->deleteProduct($id);
        if (wantsJsonResponse()) {
            respondJson([
                'message' => 'Sản phẩm đã được xóa khỏi hệ thống.',
                'deleted_id' => (int)$id,
            ]);
        }
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'title' => 'Thành công!',
            'text' => 'Sản phẩm đã được xóa khỏi hệ thống.'
        ];
        header("Location: /webbanhang/ProductController");
    }


    public function save()
    {
        requireAdmin();
        $data = $this->getRequestData();
        $name = trim((string)($data['name'] ?? ''));
        $description = trim((string)($data['description'] ?? ''));
        $price = $data['price'] ?? null;
        $category_id = $data['category_id'] ?? null;

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
            if (wantsJsonResponse()) {
                respondJson([
                    'message' => 'Dữ liệu tạo sản phẩm không hợp lệ.',
                    'errors' => $errors,
                ], 422);
            }
            $_SESSION['old_input'] = $data;
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Lỗi dữ liệu!',
                'text' => implode(' ', $errors)
            ];
            header("Location: /webbanhang/ProductController/add");
            return;
        }

        $image = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $target = "uploads/" . time() . "_" . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $image = $target;
                }
            } else {
                if (wantsJsonResponse()) {
                    respondJson([
                        'message' => 'Ảnh không hợp lệ. Chỉ cho phép định dạng: jpg, jpeg, png, gif.',
                    ], 422);
                }
                $_SESSION['old_input'] = $data;
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'title' => 'Ảnh không hợp lệ!',
                    'text' => 'Chỉ cho phép định dạng: jpg, jpeg, png, gif.'
                ];
                header("Location: /webbanhang/ProductController/add");
                return;
            }
        }

        try {
            $this->productModel->addProduct($name, $description, $price, $category_id, $image);
        } catch (Throwable $e) {
            if (wantsJsonResponse()) {
                respondJson([
                    'message' => 'Không thể lưu sản phẩm mới. Vui lòng kiểm tra lại danh mục, giá và dữ liệu nhập.'
                ], 400);
            }
            $_SESSION['old_input'] = $data;
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Thêm sản phẩm thất bại',
                'text' => 'Không thể lưu sản phẩm mới. Vui lòng kiểm tra lại danh mục, giá và dữ liệu nhập.'
            ];
            header("Location: /webbanhang/ProductController/add");
            return;
        }

        $createdProducts = $this->productModel->search($name);
        $createdProduct = !empty($createdProducts) ? $createdProducts[0] : null;

        if (wantsJsonResponse()) {
            respondJson([
                'message' => 'Sản phẩm mới đã được thêm thành công.',
                'product' => $createdProduct,
            ], 201);
        }

        unset($_SESSION['old_input']);
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

        if (wantsJsonResponse()) {
            respondJson([
                'keyword' => $keyword,
                'products' => $products,
            ]);
        }

        include 'app/views/product/list.php';
    }

    public function show($id)
    {
        $product = $this->productModel->getProductById($id);

        if (!$product) {
            if (wantsJsonResponse()) {
                respondJson(['message' => 'Không tìm thấy sản phẩm'], 404);
            }
            die("Không tìm thấy sản phẩm");
        }

        if (wantsJsonResponse()) {
            respondJson(['product' => $product]);
        }

        include 'app/views/product/show.php';
    }

    public function addToCart($id)
    {
        requireLogin();
        $this->cartModel->addItem($this->getCurrentUserId(), (int)$id, 1);

        header("Location: /webbanhang/ProductController/cart");
    }

    public function cart()
    {
        $products = $this->getCartProducts();

        include 'app/views/product/cart.php';
    }

    public function removeFromCart($id)
    {
        $accountId = $this->getCurrentUserId();

        if ($accountId) {
            $this->cartModel->removeItem($accountId, (int)$id);
        } elseif (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }

        header("Location: /webbanhang/ProductController/cart");
    }

    public function checkout($id = null)
    {
        requireLogin();

        $selected_ids = $_POST['selected_ids'] ?? null;

        if ($id) {
            // Thanh toán riêng lẻ 1 sản phẩm (từ nút "Mua ngay")
            $product = $this->productModel->getProductById($id);
            if (!$product) {
                die("Sản phẩm không tồn tại");
            }
            $cart = $this->getCartMap();
            $qty = $cart[$id] ?? 1;
            $product->qty = $qty;
            $products = [$product];
            $single_product_id = $id;
        } elseif ($selected_ids) {
            // Thanh toán các sản phẩm được chọn từ giỏ hàng
            $cart = $this->getCartMap();
            $products = [];
            foreach ($selected_ids as $pid) {
                $product = $this->productModel->getProductById($pid);
                if ($product) {
                    $product->qty = $cart[$pid] ?? 1;
                    $products[] = $product;
                }
            }
        } else {
            // Thanh toán toàn bộ giỏ hàng (nếu truy cập trực tiếp URL)
            $cart = $this->getCartMap();

            if (count($cart) == 0) {
                echo "<script>
                    alert('Giỏ hàng trống! Bạn cần thêm sản phẩm trước khi thanh toán.');
                    window.location='/webbanhang/ProductController';
                  </script>";
                exit;
            }
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
        requireLogin();
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
        $cart = $this->getCartMap();
        $accountId = $this->getCurrentUserId();

        if ($single_id) {
            $product = $this->productModel->getProductById($single_id);
            if (!$product) {
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'title' => 'Sản phẩm không tồn tại',
                    'text' => 'Không thể đặt hàng với sản phẩm đã bị xóa.'
                ];
                header("Location: /webbanhang/ProductController/cart");
                return;
            }
            $qty = $cart[$single_id] ?? 1;
            $total = $product->Price * $qty;
            $order_items[] = [
                'product_id' => (int)$single_id,
                'product_name' => $product->Name,
                'product_image' => $product->Image,
                'original_price' => (float)$product->Price,
                'sale_price' => (float)$product->Price,
                'tax_amount' => 0,
                'quantity' => (int)$qty,
                'subtotal' => (float)$product->Price * (int)$qty,
            ];
        } elseif ($selected_ids) {
            foreach ($selected_ids as $pid) {
                if (isset($cart[$pid])) {
                    $product = $this->productModel->getProductById($pid);
                    if (!$product) {
                        continue;
                    }
                    $qty = $cart[$pid];
                    $total += $product->Price * $qty;
                    $order_items[] = [
                        'product_id' => (int)$pid,
                        'product_name' => $product->Name,
                        'product_image' => $product->Image,
                        'original_price' => (float)$product->Price,
                        'sale_price' => (float)$product->Price,
                        'tax_amount' => 0,
                        'quantity' => (int)$qty,
                        'subtotal' => (float)$product->Price * (int)$qty,
                    ];
                }
            }
        } else {
            foreach ($cart as $pid => $qty) {
                $product = $this->productModel->getProductById($pid);
                if (!$product) {
                    continue;
                }
                $total += $product->Price * $qty;
                $order_items[] = [
                    'product_id' => (int)$pid,
                    'product_name' => $product->Name,
                    'product_image' => $product->Image,
                    'original_price' => (float)$product->Price,
                    'sale_price' => (float)$product->Price,
                    'tax_amount' => 0,
                    'quantity' => (int)$qty,
                    'subtotal' => (float)$product->Price * (int)$qty,
                ];
            }
        }

        if (empty($order_items)) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Giỏ hàng trống',
                'text' => 'Không có sản phẩm hợp lệ để đặt hàng.'
            ];
            header("Location: /webbanhang/ProductController/cart");
            return;
        }

        $orderPayload = [
            'total' => $total,
            'name' => $name,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
            'payment_method' => $payment_method,
            'notes' => $notes,
        ];

        try {
            $orderModel->createOrder($orderPayload, $order_items, $accountId);

            if ($single_id) {
                if ($accountId) {
                    $this->cartModel->removeItem($accountId, (int)$single_id);
                } else {
                    unset($_SESSION['cart'][$single_id]);
                }
            } elseif ($selected_ids) {
                if ($accountId) {
                    $this->cartModel->clearSelectedItems($accountId, $selected_ids);
                } else {
                    foreach ($selected_ids as $pid) {
                        unset($_SESSION['cart'][$pid]);
                    }
                }
            } else {
                if ($accountId) {
                    $this->cartModel->clearCart($accountId);
                } else {
                    unset($_SESSION['cart']);
                }
            }
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Đặt hàng thất bại',
                'text' => $e->getMessage()
            ];
            header("Location: /webbanhang/ProductController/checkout");
            return;
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
        if (!isLoggedIn()) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập để thêm vào giỏ hàng.']);
            exit;
        }
        $cart = $this->getCartMap();
        $status = isset($cart[$id]) ? 'exists' : 'success';

        $this->cartModel->addItem($this->getCurrentUserId(), (int)$id, 1);

        $count = $this->getCartCount();

        echo json_encode([
            "count" => $count,
            "status" => $status
        ]);
    }

    public function increaseCart($id)
    {
        $accountId = $this->getCurrentUserId();

        if ($accountId) {
            $this->cartModel->addItem($accountId, (int)$id, 1);
        } elseif (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]++;
        }

        header("Location: /webbanhang/ProductController/cart");
    }

    public function decreaseCart($id)
    {
        $accountId = $this->getCurrentUserId();

        if ($accountId) {
            $cart = $this->getCartMap();
            if (isset($cart[$id])) {
                $this->cartModel->setItemQuantity($accountId, (int)$id, $cart[$id] - 1);
            }
        } elseif (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]--;

            if ($_SESSION['cart'][$id] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        }

        header("Location: /webbanhang/ProductController/cart");
    }

    public function updateCartAjax()
    {
        $id = $_POST['id'] ?? null;
        $qty = (int)($_POST['qty'] ?? 1);

        if ($id) {
            if ($qty < 1) {
                $qty = 1; // Luôn giữ tối thiểu là 1 thay vì xóa sản phẩm khi nhập 0
            }

            $accountId = $this->getCurrentUserId();
            if ($accountId) {
                $this->cartModel->setItemQuantity($accountId, (int)$id, $qty);
            } else {
                $_SESSION['cart'][$id] = $qty;
            }
        }

        $total = 0;
        foreach ($this->getCartMap() as $pid => $q) {
            $product = $this->productModel->getProductById($pid);
            if ($product) {
                $total += $product->Price * $q;
            }
        }

        $count = $this->getCartCount();

        echo json_encode([
            "total" => number_format($total) . " VND",
            "count" => $count
        ]);
    }

    public function buyNow($id)
    {
        requireLogin();
        header("Location: /webbanhang/ProductController/checkout/$id");
        exit;
    }
}

