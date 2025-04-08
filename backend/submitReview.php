<?php
// Start session to access user information
session_start();

// Set header to return JSON
header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'You must be logged in to submit a review']);
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Check if all required parameters are present
if (!isset($_POST['product_id']) || !isset($_POST['review_text']) || !isset($_POST['rating'])) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

// Get form data
$product_id = intval($_POST['product_id']);
$review_text = trim($_POST['review_text']);
$rating = intval($_POST['rating']);

// Validate data
if ($product_id <= 0) {
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

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
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Process image upload if present
$image_path = null;
if (isset($_FILES['review_image']) && $_FILES['review_image']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    // Validate file type and size
    if (!in_array($_FILES['review_image']['type'], $allowed_types)) {
        echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, and GIF are allowed']);
        exit;
    }
    
    if ($_FILES['review_image']['size'] > $max_size) {
        echo json_encode(['error' => 'File is too large. Maximum size is 2MB']);
        exit;
    }
    
    // Generate unique filename
    $extension = pathinfo($_FILES['review_image']['name'], PATHINFO_EXTENSION);
    $filename = 'review_' . time() . '_' . uniqid() . '.' . $extension;
    $upload_path = '../uploads/reviews/' . $filename;
    
    // Create directory if it doesn't exist
    if (!file_exists('../uploads/reviews/')) {
        mkdir('../uploads/reviews/', 0777, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($_FILES['review_image']['tmp_name'], $upload_path)) {
        $image_path = 'uploads/reviews/' . $filename;
    } else {
        echo json_encode(['error' => 'Failed to upload image']);
        exit;
    }
}

// Insert review into database
$stmt = $conn->prepare("
    INSERT INTO feedback (product_id, user_id, comment, rating, image_path, feedback_date) 
    VALUES (?, ?, ?, ?, ?, NOW())
");

if (!$stmt) {
    echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("iisis", $product_id, $user_id, $review_text, $rating, $image_path);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Failed to submit review: ' . $stmt->error]);
    exit;
}

// Get the newly created review ID
$review_id = $conn->insert_id;

// Fetch the new review to return to the client
$get_review = $conn->prepare("
    SELECT f.*, u.username 
    FROM feedback f
    LEFT JOIN users u ON f.user_id = u.user_id
    WHERE f.feedback_id = ?
");

$get_review->bind_param("i", $review_id);
$get_review->execute();
$review_result = $get_review->get_result();
$new_review = $review_result->fetch_assoc();

// Format the date
$date = new DateTime($new_review['feedback_date']);
$new_review['formatted_date'] = $date->format('F j, Y, g:i a');

// Close connections
$stmt->close();
$get_review->close();
$conn->close();

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Review submitted successfully',
    'review' => $new_review
]);
?>