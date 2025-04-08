<?php
// fetchReview.php - handles submitting new reviews
header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'You must be logged in to leave a review']);
    exit;
}

// Get form data
$user_id = $_SESSION['user_id'];
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 5;
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 1; // Default to product 1 if not specified

// Validate input
if (empty($review_text)) {
    echo json_encode(['error' => 'Review text cannot be empty']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['error' => 'Rating must be between 1 and 5']);
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
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Check if user already reviewed this product
$check_sql = "SELECT feedback_id FROM product_feedback WHERE product_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $product_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Update existing review
    $feedback_id = $check_result->fetch_assoc()['feedback_id'];
    $sql = "UPDATE product_feedback SET comment = ?, rating = ?, feedback_date = NOW() WHERE feedback_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $review_text, $rating, $feedback_id);
} else {
    // Insert new review
    $sql = "INSERT INTO product_feedback (product_id, user_id, comment, rating) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisi", $product_id, $user_id, $review_text, $rating);
}

if ($stmt->execute()) {
    // Get the review ID (either the new one or the existing one)
    $feedback_id = $stmt->insert_id > 0 ? $stmt->insert_id : $feedback_id;
    
    // Fetch the complete review data including username
    $get_review_sql = "SELECT f.*, u.username FROM product_feedback f 
                       JOIN userInfo_db.users u ON f.user_id = u.user_id
                       WHERE f.feedback_id = ?";
    $get_review_stmt = $conn->prepare($get_review_sql);
    $get_review_stmt->bind_param("i", $feedback_id);
    $get_review_stmt->execute();
    $review = $get_review_stmt->get_result()->fetch_assoc();
    
    echo json_encode(['success' => true, 'review' => $review]);
    $get_review_stmt->close();
} else {
    echo json_encode(['error' => 'Failed to save review: ' . $stmt->error]);
}

$stmt->close();
$check_stmt->close();
$conn->close();
?>