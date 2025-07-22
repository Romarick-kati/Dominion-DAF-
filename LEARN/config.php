<?php
// config/database.php - Database Configuration
class Database {
    private $host = "localhost";
    private $database_name = "warehouse_management";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->database_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// api/auth.php - Authentication API
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));
$action = $_GET['action'] ?? '';

switch($action) {
    case 'login':
        if (!empty($data->username) && !empty($data->password)) {
            $query = "SELECT id, username, email, role, password FROM users WHERE username = ? LIMIT 0,1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $data->username);
            $stmt->execute();
            $num = $stmt->rowCount();

            if ($num > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($data->password, $row['password'])) {
                    $token = base64_encode($row['id'] . ':' . $row['username']);
                    http_response_code(200);
                    echo json_encode(array(
                        "message" => "Login successful.",
                        "token" => $token,
                        "user" => array(
                            "id" => $row['id'],
                            "username" => $row['username'],
                            "email" => $row['email'],
                            "role" => $row['role']
                        )
                    ));
                } else {
                    http_response_code(401);
                    echo json_encode(array("message" => "Login failed."));
                }
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "Login failed."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to login. Data is incomplete."));
        }
        break;

    case 'register':
        if (!empty($data->username) && !empty($data->password) && !empty($data->email)) {
            $query = "INSERT INTO users SET username = ?, password = ?, email = ?, role = ?";
            $stmt = $db->prepare($query);

            $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
            
            $stmt->bindParam(1, $data->username);
            $stmt->bindParam(2, $hashed_password);
            $stmt->bindParam(3, $data->email);
            $stmt->bindParam(4, $data->role);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "User was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create user."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create user. Data is incomplete."));
        }
        break;
}
?>

// api/suppliers.php - Suppliers API
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

switch($method) {
    case 'GET':
        $query = "SELECT id, name, email, phone, address, created_at FROM suppliers ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $suppliers = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $suppliers[] = $row;
        }
        
        http_response_code(200);
        echo json_encode($suppliers);
        break;

    case 'POST':
        if (!empty($data->name) && !empty($data->email)) {
            $query = "INSERT INTO suppliers SET name = ?, email = ?, phone = ?, address = ?";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(1, $data->name);
            $stmt->bindParam(2, $data->email);
            $stmt->bindParam(3, $data->phone);
            $stmt->bindParam(4, $data->address);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Supplier was created.", "id" => $db->lastInsertId()));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create supplier."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create supplier. Data is incomplete."));
        }
        break;

    case 'PUT':
        if (!empty($data->id) && !empty($data->name) && !empty($data->email)) {
            $query = "UPDATE suppliers SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(1, $data->name);
            $stmt->bindParam(2, $data->email);
            $stmt->bindParam(3, $data->phone);
            $stmt->bindParam(4, $data->address);
            $stmt->bindParam(5, $data->id);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Supplier was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update supplier."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update supplier. Data is incomplete."));
        }
        break;

    case 'DELETE':
        if (!empty($data->id)) {
            $query = "DELETE FROM suppliers WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $data->id);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Supplier was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete supplier."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete supplier. Data is incomplete."));
        }
        break;
}
?>

// api/warehouses.php - Warehouses API
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

switch($method) {
    case 'GET':
        $query = "SELECT id, name, location, capacity, manager, created_at FROM warehouses ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $warehouses = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $warehouses[] = $row;
        }
        
        http_response_code(200);
        echo json_encode($warehouses);
        break;

    case 'POST':
        if (!empty($data->name) && !empty($data->location)) {
            $query = "INSERT INTO warehouses SET name = ?, location = ?, capacity = ?, manager = ?";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(1, $data->name);
            $stmt->bindParam(2, $data->location);
            $stmt->bindParam(3, $data->capacity);
            $stmt->bindParam(4, $data->manager);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Warehouse was created.", "id" => $db->lastInsertId()));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create warehouse."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create warehouse. Data is incomplete."));
        }
        break;

    case 'DELETE':
        if (!empty($data->id)) {
            $query = "DELETE FROM warehouses WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $data->id);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Warehouse was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete warehouse."));
            }
        }
        break;
}
?>

// api/products.php - Products API
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

switch($method) {
    case 'GET':
        $query = "SELECT p.id, p.name, p.price, p.quantity, p.description, p.created_at, 
                  s.name as supplier_name 
                  FROM products p 
                  LEFT JOIN suppliers s ON p.supplier_id = s.id 
                  ORDER BY p.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $products = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $row;
        }
        
        http_response_code(200);
        echo json_encode($products);
        break;

    case 'POST':
        if (!empty($data->name) && !empty($data->price)) {
            $query = "INSERT INTO products SET name = ?, price = ?, quantity = ?, supplier_id = ?, description = ?";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(1, $data->name);
            $stmt->bindParam(2, $data->price);
            $stmt->bindParam(3, $data->quantity);
            $stmt->bindParam(4, $data->supplier_id);
            $stmt->bindParam(5, $data->description);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Product was created.", "id" => $db->lastInsertId()));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create product."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create product. Data is incomplete."));
        }
        break;

    case 'DELETE':
        if (!empty($data->id)) {
            $query = "DELETE FROM products WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $data->id);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Product was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete product."));
            }
        }
        break;
}
?>

// api/orders.php - Orders API
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

switch($method) {
    case 'GET':
        $query = "SELECT o.id, o.quantity, o.total_amount, o.customer_name, o.customer_email, 
                  o.status, o.order_date, p.name as product_name, p.price
                  FROM orders o 
                  LEFT JOIN products p ON o.product_id = p.id 
                  ORDER BY o.order_date DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $orders = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $orders[] = $row;
        }
        
        http_response_code(200);
        echo json_encode($orders);
        break;

    case 'POST':
        if (!empty($data->product_id) && !empty($data->quantity)) {
            // Get product price
            $productQuery = "SELECT price FROM products WHERE id = ?";
            $productStmt = $db->prepare($productQuery);
            $productStmt->bindParam(1, $data->product_id);
            $productStmt->execute();
            $product = $productStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                $total = $product['price'] * $data->quantity;
                
                $query = "INSERT INTO orders SET product_id = ?, quantity = ?, total_amount = ?, customer_name = ?, customer_email = ?, status = 'Pending'";
                $stmt = $db->prepare($query);
                
                $stmt->bindParam(1, $data->product_id);
                $stmt->bindParam(2, $data->quantity);
                $stmt->bindParam(3, $total);
                $stmt->bindParam(4, $data->customer_name);
                $stmt->bindParam(5, $data->customer_email);

                if ($stmt->execute()) {
                    http_response_code(201);
                    echo json_encode(array("message" => "Order was created.", "id" => $db->lastInsertId()));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Unable to create order."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Product not found."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create order. Data is incomplete."));
        }
        break;

    case 'DELETE':
        if (!empty($data->id)) {
            $query = "DELETE FROM orders WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $data->id);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Order was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete order."));
            }
        }
        break;
}
?>