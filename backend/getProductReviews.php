<?php
// Start session to access user information
session_start();

// Set header to return JSON
header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Get the product ID from the URL parameter
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
if ($product_id <= 0) {
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "root"; 
$dbname = "productInfo_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Query to get product reviews
$stmt = $conn->prepare("
    SELECT f.*, u.username 
    FROM feedback f
    LEFT JOIN users u ON f.user_id = u.user_id
    WHERE f.product_id = ?
    ORDER BY f.feedback_date DESC
");

if (!$stmt) {
    echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

// Store reviews in an array
$reviews = [];
while ($row = $result->fetch_assoc()) {
    // Format the date for easier reading
    $date = new DateTime($row['feedback_date']);
    $row['formatted_date'] = $date->format('F j, Y, g:i a');
    
    $reviews[] = $row;
}

// Close connection
$stmt->close();
$conn->close();

// Return reviews as JSON
echo json_encode([
    'reviews' => $reviews,
    'count' => count($reviews)
]);
?>