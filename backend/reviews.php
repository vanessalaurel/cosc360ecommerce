<?php
// reviews.php - place this in your backend folder
header('Content-Type: application/json');
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "productInfo_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Get product ID from request
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 1;
$last_feedback_id = isset($_GET['last_feedback_id']) ? intval($_GET['last_feedback_id']) : 0;

if ($product_id <= 0) {
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

// Query to get new reviews since the last one shown
$sql = "SELECT f.feedback_id, f.comment, f.rating, f.feedback_date, 
        u.username, u.user_id 
        FROM product_feedback f
        JOIN userInfo_db.users u ON f.user_id = u.user_id
        WHERE f.product_id = ? AND f.feedback_id > ?
        ORDER BY f.feedback_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $last_feedback_id);
$stmt->execute();
$result = $stmt->get_result();

$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}

echo json_encode(['reviews' => $reviews]);

$stmt->close();
$conn->close();
?>