<?php
// submit_review.php - place this in your backend folder
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

// Get form data
$product_id = 1;
$comment = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 5; // Default to 5 if not provided
$user_id = $_SESSION['user_id'];

// Validate input
if (empty($comment)) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

//Check if user has already reviewed this product
$check_sql = "SELECT feedback_id FROM product_feedback 
              WHERE product_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $product_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Update existing review
    $row = $check_result->fetch_assoc();
    $feedback_id = $row['feedback_id'];
    
    $update_sql = "UPDATE product_feedback 
                  SET comment = ?, rating = ?, feedback_date = CURRENT_TIMESTAMP 
                  WHERE feedback_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sii", $comment, $rating, $feedback_id);
    
    if ($update_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Review updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update review']);
    }
    $update_stmt->close();
} else {
    // Insert new review
    $insert_sql = "INSERT INTO product_feedback (product_id, user_id, rating, comment) 
                  VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
    
    if ($insert_stmt->execute()) {
        // Get the ID of the newly inserted review
        $new_feedback_id = $conn->insert_id;
        
        // Fetch the complete review with username
        $fetch_sql = "SELECT f.feedback_id, f.comment, f.rating, f.feedback_date, 
                     u.username, u.user_id 
                     FROM product_feedback f
                     JOIN userInfo_db.users u ON f.user_id = u.user_id
                     WHERE f.feedback_id = ?";
        $fetch_stmt = $conn->prepare($fetch_sql);
        $fetch_stmt->bind_param("i", $new_feedback_id);
        $fetch_stmt->execute();
        $review = $fetch_stmt->get_result()->fetch_assoc();
        
        echo json_encode(['success' => true, 'message' => 'Review submitted successfully', 'review' => $review]);
        $fetch_stmt->close();
    } else {
        echo json_encode(['error' => 'Failed to submit review']);
    }
    $insert_stmt->close();
}

$check_stmt->close();
$conn->close();
?>