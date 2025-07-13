
<?php
require_once "database.php";

header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 0); // Don't display errors to users
error_reporting(E_ALL);
file_put_contents('debug.log', date('Y-m-d H:i:s') . " - search_products.php accessed\n", FILE_APPEND);

try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "No connection object"));
    }

    if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
        $search_term = trim($_GET['search']);
        file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Search term: $search_term\n", FILE_APPEND);

        $stmt = $conn->prepare("SELECT p.*, c.category_name 
                               FROM products p 
                               JOIN categories c ON p.category_id = c.category_id 
                               WHERE p.product_name LIKE ? OR p.product_description LIKE ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $like_term = '%' . $search_term . '%';
        $stmt->bind_param("ss", $like_term, $like_term);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = [
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'category_name' => $row['category_name'],
                'price' => number_format($row['price'], 2),
                'product_image' => $row['product_image']
            ];
        }

        $stmt->close();
        echo json_encode(['success' => true, 'products' => $products]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No search term provided']);
    }
} catch (Exception $e) {
    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
$conn->close();
?>