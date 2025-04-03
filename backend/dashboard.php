<?php
// Database 1: User Database (signup)
$userDB = new mysqli("localhost", "root", "", "userInfo_db");
if ($userDB->connect_error) {
    die("User DB Connection failed: " . $userDB->connect_error);
}

// Database 2: Product Database (products_db)
$productDB = new mysqli("localhost", "root", "root", "productInfo_db");
if ($productDB->connect_error) {
    die("Product DB Connection failed: " . $productDB->connect_error);
}

// Fetch all users from `signup` database
$sqlUsers = "SELECT id, name, email, profile_image FROM users";
$resultUsers = $userDB->query($sqlUsers);

// Fetch posts for a selected user (if clicked)
$selectedUserId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$posts = [];

if ($selectedUserId) {
    $sqlPosts = "SELECT id, title, content, images FROM products WHERE user_id = $selectedUserId";
    $resultPosts = $productDB->query($sqlPosts);
    while ($row = $resultPosts->fetch_assoc()) {
        $posts[] = $row;
    }
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
          if ($resultUsers->num_rows > 0) {
              while ($row = $resultUsers->fetch_assoc()) {
                  $profileImage = $row['profile_image'] ? "uploads/{$row['profile_image']}" : "source/profile.jpeg";
                  echo "<div class='user-item' onclick='selectUser({$row['id']})'>
                          <img src='{$profileImage}' alt='{$row['name']}'>
                          <div>
                            <p><strong>{$row['name']}</strong></p>
                            <p>Email: {$row['email']}</p>
                          </div>
                          <button class='remove-user' onclick='removeUser({$row['id']})'>Remove User</button>
                        </div>";
              }
          } else {
              echo "<p>No users registered yet.</p>";
          }
          ?>
        </div>
      </div>

      <div class="main-content">
        <h2>Post Management</h2>
        <div class="search-bar">
          <input type="text" placeholder="Search Posts" id="post-search-input" onkeyup="filterPosts()" />
        </div>
        <div class="post-list" id="post-list">
          <?php
          if ($selectedUserId && !empty($posts)) {
              foreach ($posts as $post) {
                  $images = explode(',', $post['images']); // Assuming images are stored as "img1.jpg,img2.jpg"
                  echo "<div class='post-item'>
                          <h3>{$post['title']}</h3>
                          <p>{$post['content']}</p>
                          <div class='post-images'>";
                  foreach ($images as $image) {
                      echo "<img src='uploads/$image' width='80' height='80'>";
                  }
                  echo "</div>
                          <button class='remove-post' onclick='removePost({$post['id']})'>Remove</button>
                        </div>";
              }
          } else {
              echo "<p>Select a user to view their posts.</p>";
          }
          ?>
        </div>
      </div>
    </div>

    <script>
      function selectUser(userId) {
        window.location.href = 'dashboard.php?user_id=' + userId;
      }

      function filterPosts() {
        let input = document.getElementById("post-search-input").value.toLowerCase();
        let posts = document.querySelectorAll(".post-item");

        posts.forEach(post => {
          let title = post.querySelector("h3").innerText.toLowerCase();
          post.style.display = title.includes(input) ? "block" : "none";
        });
      }

     function removeUser(userId) {
    if (confirm("Are you sure you want to remove this user?")) {
        fetch('removeuser.php?id=' + userId)
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            });
    }
}

function removePost(postId) {
    if (confirm("Are you sure you want to remove this post?")) {
        fetch('removepost.php?id=' + postId)
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
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
