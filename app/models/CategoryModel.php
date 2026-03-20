<?php
class CategoryModel{
    private $conn;
    private $table_name = "category";

    public function __construct($db){
        $this->conn = $db;
    }

    public function getCategories(){
        // Sử dụng alias để tương thích với view hiện tại đang dùng $cat->id, $cat->name
        $query = "SELECT Id as id, Name as name, Description as description FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function addCategory($name, $description){
        $query = "INSERT INTO " . $this->table_name . " (Name, Description) VALUES (:name, :description)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        return $stmt->execute();
    }
}
?>