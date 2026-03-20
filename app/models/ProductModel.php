<?php
class ProductModel
{

    private $conn;
    private $table_name = "product";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getProducts()
    {

        $query = "SELECT p.Id,
        p.Name,
        p.Description,
        p.Price,
        p.Image,
        c.Name AS category_name
        FROM product p
        LEFT JOIN category c ON p.Category_Id = c.Id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getProductById($id)
    {
        $query = "SELECT * FROM product WHERE Id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function updateProduct($id, $name, $description, $price, $category_id, $image)
    {

        $query = "UPDATE product
          SET Name = :name,
              Description = :description,
              Price = :price,
              Category_Id = :category_id,
              Image = :image
          WHERE Id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":image", $image);

        return $stmt->execute();
    }

    public function deleteProduct($id)
    {

        $query = "DELETE FROM product WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function isProductInOrder($product_id)
    {
        $query = "SELECT COUNT(*) as count FROM order_details WHERE Product_Id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }


    public function addProduct($name, $description, $price, $category_id, $image)
    {
        $query = "INSERT INTO product (Name, Description, Price, Category_Id, Image)
              VALUES (:name, :description, :price, :category_id, :image)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":image", $image);

        return $stmt->execute();
    }

    public function getProductsByPage($limit, $offset)
    {
        $query = "SELECT p.Id, p.Name, p.Description, p.Price, p.Image,
              c.Name as category_name
              FROM product p
              LEFT JOIN category c ON p.Category_Id = c.Id
              LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getFilteredProducts($limit, $offset, $category_id = null, $min_price = null, $max_price = null)
    {
        $query = "SELECT p.Id, p.Name, p.Description, p.Price, p.Image,
                  c.Name as category_name
                  FROM product p
                  LEFT JOIN category c ON p.Category_Id = c.Id
                  WHERE 1=1";
        
        if ($category_id) $query .= " AND p.Category_Id = :category_id";
        if ($min_price) $query .= " AND p.Price >= :min_price";
        if ($max_price) $query .= " AND p.Price <= :max_price";
        
        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if ($category_id) $stmt->bindParam(':category_id', $category_id);
        if ($min_price) $stmt->bindParam(':min_price', $min_price);
        if ($max_price) $stmt->bindParam(':max_price', $max_price);
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function countFilteredProducts($category_id = null, $min_price = null, $max_price = null)
    {
        $query = "SELECT COUNT(*) as total FROM product WHERE 1=1";
        if ($category_id) $query .= " AND Category_Id = :category_id";
        if ($min_price) $query .= " AND Price >= :min_price";
        if ($max_price) $query .= " AND Price <= :max_price";

        $stmt = $this->conn->prepare($query);
        if ($category_id) $stmt->bindParam(':category_id', $category_id);
        if ($min_price) $stmt->bindParam(':min_price', $min_price);
        if ($max_price) $stmt->bindParam(':max_price', $max_price);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function countProducts()
    {
        $query = "SELECT COUNT(*) as total FROM product";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }

    public function search($keyword)
    {
        $query = "SELECT Id, Name, Description, Price, Image
              FROM product
              WHERE Name LIKE :keyword";

        $stmt = $this->conn->prepare($query);

        $keyword = "%" . $keyword . "%";

        $stmt->bindParam(':keyword', $keyword);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
