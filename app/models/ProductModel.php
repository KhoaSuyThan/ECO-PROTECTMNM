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
        $query = "SELECT p.*, c.name as category_name
        FROM " . $this->table_name . " p
        LEFT JOIN category c ON p.category_id = c.id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $result;
    }
    public function getProductById($id)
    {
        $query = "SELECT p.*, c.name as category_name FROM " . $this->table_name . " p LEFT JOIN category c ON p.category_id = c.id WHERE p.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result;
    }
    public function addProduct($name, $description, $price, $category_id, $image = '')
    {
        $errors = [];
        if (empty($name)) {
            $errors['name'] = 'Tên sản phẩm không được để trống';
        }
        if (empty($description)) {
            $errors['description'] = 'Mô tả không được để trống';
        }
        if (!is_numeric($price) || $price < 0) {
            $errors['price'] = 'Giá sản phẩm không hợp lệ';
        }
        if (count($errors) > 0) {
            return $errors;
        }
        $query = "INSERT INTO " . $this->table_name . " (name, description, price, category_id, image) VALUES (:name, :description, :price, :category_id, :image)";
        $stmt = $this->conn->prepare($query);
        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $price = htmlspecialchars(strip_tags($price));
        $category_id = htmlspecialchars(strip_tags($category_id));
        $image = htmlspecialchars(strip_tags($image));

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image', $image);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateProduct($id, $name, $description, $price, $category_id, $image = null)
    {
        if ($image !== null) {
            $query = "UPDATE " . $this->table_name . " SET name=:name, description=:description, price=:price, category_id=:category_id, image=:image WHERE id=:id";
        } else {
            $query = "UPDATE " . $this->table_name . " SET name=:name, description=:description, price=:price, category_id=:category_id WHERE id=:id";
        }
        $stmt = $this->conn->prepare($query);
        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $price = htmlspecialchars(strip_tags($price));
        $category_id = htmlspecialchars(strip_tags($category_id));

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        if ($image !== null) {
            $image = htmlspecialchars(strip_tags($image));
            $stmt->bindParam(':image', $image);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function deleteProduct($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getProductsAdvanced($params) {
        $query = "SELECT p.*, c.name as category_name FROM " . $this->table_name . " p LEFT JOIN category c ON p.category_id = c.id WHERE 1=1";
        $bindParams = [];

        if (!empty($params['name'])) {
            $query .= " AND p.name LIKE :name";
            $bindParams[':name'] = "%" . $params['name'] . "%";
        }

        if (!empty($params['category_id'])) {
            $query .= " AND p.category_id = :category_id";
            $bindParams[':category_id'] = $params['category_id'];
        }

        if (isset($params['min_price']) && is_numeric($params['min_price'])) {
            $query .= " AND p.price >= :min_price";
            $bindParams[':min_price'] = $params['min_price'];
        }

        if (isset($params['max_price']) && is_numeric($params['max_price'])) {
            $query .= " AND p.price <= :max_price";
            $bindParams[':max_price'] = $params['max_price'];
        }

        // Sorting
        $sort = $params['sort'] ?? '';
        if ($sort === 'price_asc') {
            $query .= " ORDER BY p.price ASC";
        } elseif ($sort === 'price_desc') {
            $query .= " ORDER BY p.price DESC";
        } else {
            $query .= " ORDER BY p.created_at DESC";
        }

        // Pagination
        if (isset($params['limit']) && isset($params['offset'])) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->conn->prepare($query);
        foreach ($bindParams as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        if (isset($params['limit']) && isset($params['offset'])) {
            $stmt->bindValue(':limit', (int)$params['limit'], PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$params['offset'], PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public function countProductsAdvanced($params) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " p WHERE 1=1";
        $bindParams = [];

        if (!empty($params['name'])) {
            $query .= " AND p.name LIKE :name";
            $bindParams[':name'] = "%" . $params['name'] . "%";
        }

        if (!empty($params['category_id'])) {
            $query .= " AND p.category_id = :category_id";
            $bindParams[':category_id'] = $params['category_id'];
        }

        if (isset($params['min_price']) && is_numeric($params['min_price'])) {
            $query .= " AND p.price >= :min_price";
            $bindParams[':min_price'] = $params['min_price'];
        }

        if (isset($params['max_price']) && is_numeric($params['max_price'])) {
            $query .= " AND p.price <= :max_price";
            $bindParams[':max_price'] = $params['max_price'];
        }

        $stmt = $this->conn->prepare($query);
        foreach ($bindParams as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_OBJ);
        return $res ? (int)$res->total : 0;
    }
}
?>