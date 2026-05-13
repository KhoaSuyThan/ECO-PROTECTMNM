<?php
class ProductModel {
    private $conn;
    private $table_name = "product";

    public $id, $name, $description, $price, $image, $category_id, $category_name; 

    public function __construct($db = null) {
        $this->conn = $db;
    }

    public function getProducts() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN category c ON p.category_id = c.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        // Thay FETCH_CLASS bằng FETCH_OBJ
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getProductById($id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN category c ON p.category_id = c.id 
                  WHERE p.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Thay FETCH_CLASS bằng FETCH_OBJ
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function addProduct($name, $description, $price, $category_id, $image) {
        $query = "INSERT INTO " . $this->table_name . " (name, description, price, category_id, image) 
                  VALUES (:name, :description, :price, :category_id, :image)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':name' => htmlspecialchars(strip_tags($name)),
            ':description' => htmlspecialchars(strip_tags($description)),
            ':price' => $price,
            ':category_id' => $category_id,
            ':image' => $image
        ]);
    }

    public function updateProduct($id, $name, $description, $price, $category_id, $image) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name=:name, description=:description, price=:price, 
                      category_id=:category_id, image=:image 
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id' => $id,
            ':name' => htmlspecialchars(strip_tags($name)),
            ':description' => htmlspecialchars(strip_tags($description)),
            ':price' => $price,
            ':category_id' => $category_id,
            ':image' => $image
        ]);
    }

    public function deleteProduct($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}