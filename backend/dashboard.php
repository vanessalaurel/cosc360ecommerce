<?php
// Database 1: User Database (userInfo_db)
$userDB = new mysqli("localhost", "root", "root", "userInfo_db");
if ($userDB->connect_error) {
    die("User DB Connection failed: " . $userDB->connect_error);
}

// Database 2: Product Database (productInfo_db)
$productDB = new mysqli("localhost", "root", "root", "productInfo_db");
if ($productDB->connect_error) {
    die("Product DB Connection failed: " . $productDB->connect_error);
}

// Fetch all users from `userInfo_db` database - using the correct column names from your table
$sqlUsers = "SELECT user_id, username, email FROM users";
$resultUsers = $userDB->query($sqlUsers);

// Fetch products for a selected user (if clicked)
$selectedUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$products = [];

if ($selectedUserId) {
    // Adjusted column names to match products table structure
    $sqlProducts = "SELECT product_id, product_name, description, category, price, quantity, image_path 
                   FROM products WHERE user_id = ?";
    
    $stmt = $productDB->prepare($sqlProducts);
    $stmt->bind_param("i", $selectedUserId);
    $stmt->execute();
    $resultProducts = $stmt->get_result();
    
    while ($row = $resultProducts->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Administrator Dashboard</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
      body {
        background-color: #d96c53;
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
      }
      header {
        background-color: #000;
        color: white;
        padding: 10px 20px;
        text-align: center;
      }
      .container {
        display: flex;
        justify-content: space-between;
        padding: 20px;
      }
      .sidebar {
        width: 25%;
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      }
      .sidebar h2 {
        text-align: center;
        font-size: 24px;
      }
      .user-list {
        margin-top: 20px;
      }
      .user-item {
        display: flex;
        align-items: center;
        background-color: #fff;
        margin: 10px 0;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        cursor: pointer;
      }
      .user-item img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin-right: 10px;
      }
      .main-content {
        width: 950px;
        padding: 20px;
        background-color: white;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      }
      .post-item {
        background-color: #fff;
        margin: 10px 0;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        position: relative;
      }
      .remove-post {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #e53e3e;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 5px;
      }
      .remove-user {
        background-color: #e53e3e;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 5px;
        margin-left: auto;
      }
      .product-details {
        display: flex;
        flex-wrap: wrap;
        margin-top: 10px;
      }
      .product-detail {
        background-color: #f8f8f8;
        padding: 5px 10px;
        margin-right: 10px;
        margin-bottom: 5px;
        border-radius: 3px;
        font-size: 14px;
      }
    </style>
  </head>
  <body>
    <header>
      <h1>Admin Dashboard</h1>
    </header>
    <div class="container">
      <div class="sidebar">
        <h2>User Management</h2>
        <div class="user-list">
          <?php
          if ($resultUsers && $resultUsers->num_rows > 0) {
              while ($row = $resultUsers->fetch_assoc()) {
                  // Use a default profile image since your table might not have profile images
                  $profileImage = "Source/productEcomm.png";
                  echo "<div class='user-item' onclick='selectUser({$row['user_id']})'>
                          <img src='{$profileImage}' alt='{$row['username']}'>
                          <div>
                            <p><strong>{$row['username']}</strong></p>
                            <p>Email: {$row['email']}</p>
                          </div>
                          <button class='remove-user' onclick='event.stopPropagation(); removeUser({$row['user_id']})'>Remove</button>
                        </div>";
              }
          } else {
              echo "<p>No users registered yet.</p>";
          }
          ?>
        </div>
      </div>

      <div class="main-content">
        <h2>Product Management</h2>
        <div class="search-bar">
          <input type="text" placeholder="Search Products" id="product-search-input" onkeyup="filterProducts()" />
        </div>
        <div class="post-list" id="product-list">
          <?php
          if ($selectedUserId && !empty($products)) {
              foreach ($products as $product) {
                  echo "<div class='post-item'>
                          <h3>{$product['product_name']}</h3>
                          <p>{$product['description']}</p>
                          <div class='product-details'>
                            <span class='product-detail'>Price: \${$product['price']}</span>
                            <span class='product-detail'>Category: {$product['category']}</span>
                            <span class='product-detail'>Quantity: {$product['quantity']}</span>
                          </div>
                          <div class='post-images'>";
                  
                  // Display product image
                  $imagePath = $product['image_path'] ? $product['image_path'] : "Source/productEcomm.png";
                  echo "<img src='{$imagePath}' width='80' height='80' onerror=\"this.src='Source/productEcomm.png';\">";
                  
                  echo "</div>
                          <button class='remove-post' onclick='removeProduct({$product['product_id']})'>Remove</button>
                        </div>";
              }
          } else {
              echo "<p>Select a user to view their products.</p>";
          }
          ?>
        </div>
      </div>
    </div>

    <script>
      function selectUser(userId) {
        window.location.href = 'dashboard.php?user_id=' + userId;
      }

      function filterProducts() {
        let input = document.getElementById("product-search-input").value.toLowerCase();
        let products = document.querySelectorAll(".post-item");

        products.forEach(product => {
          let title = product.querySelector("h3").innerText.toLowerCase();
          product.style.display = title.includes(input) ? "block" : "none";
        });
      }

      function removeUser(userId) {
        if (confirm("Are you sure you want to remove this user? All their products will also be deleted.")) {
          fetch('removeUserinAdmin.php?user_id=' + userId)
            .then(response => response.text())
            .then(data => {
              alert(data);
              location.reload();
            })
            .catch(error => {
              console.error('Error:', error);
              alert('Failed to remove user');
            });
        }
      }

      function removeProduct(productId) {
        if (confirm("Are you sure you want to remove this product?")) {
          fetch('removeProductinAdmin.php?product_id=' + productId)
            .then(response => response.text())
            .then(data => {
              alert(data);
              location.reload();
            })
            .catch(error => {
              console.error('Error:', error);
              alert('Failed to remove product');
            });
        }
      }
    </script>
  </body>
</html>

<?php
$userDB->close();
$productDB->close();
?>
