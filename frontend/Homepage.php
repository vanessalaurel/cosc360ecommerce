<?php
// Start the session if needed
session_start();

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "productInfo_db";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all products - added product_id to the query
$sql = "SELECT product_id, product_name, price, description, category, quantity, image_path FROM products";
$result = $conn->query($sql);

// Debug image paths
$debug_paths = [];
if ($result && $result->num_rows > 0) {
    $temp_result = $result;
    while ($row = $temp_result->fetch_assoc()) {
        $debug_paths[] = $row['image_path'];
    }
    // Reset result pointer
    $result->data_seek(0);
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>E-Commerce HomePage</title>
    <link rel="stylesheet" href="styles.css" />
    <script
      src="https://kit.fontawesome.com/3d43a7b60d.js"
      crossorigin="anonymous"
    ></script>
    <style>
      /* Add styles for clickable product cards */
      .product-card {
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
      }
      
      .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
      }
      
      body {
        background-color: #d96c53;
      }
      
      /* Add fallback styling for missing images */
      .product-image {
        position: relative;
        background-color: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      
      .product-image::before {
        content: "Product Image";
        position: absolute;
        color: #999;
        font-style: italic;
        z-index: 0;
      }
      
      .product-image img {
        position: relative;
        z-index: 1;
      }
    </style>
  </head>
  <body>
    <!-- Debug information - hidden from display -->
    <div style="display: none;" id="debug-info">
      <?php 
        echo "<!-- Image paths in database: -->\n";
        foreach ($debug_paths as $path) {
          echo "<!-- Path: " . htmlspecialchars($path) . " -->\n";
        }
      ?>
    </div>
    
    <header>
      <div class="logo"><img src="Source/Exclusive.png" alt="Logo" /></div>
      <nav>
        <a href="#">Home</a>
        <a href="#">Contact</a>
        <a href="#">About</a>
        <a href="#" class="active">Sign Up</a>
      </nav>
      <div class="search-cart">
        <input type="text" placeholder="What are you looking for?" />
        <button><img src="Source/Vector.png" alt="" /></button>
        <button>
          <img
            src="Source/Frame 551.png"
            style="position: relative; top: 7px"
          />
        </button>
        <a href="Profilepage.html">
          <button>
            <img src="Source/user.png" style="position: relative; top: 7px" />
          </button>
        </a>
      </div>
    </header>

    <section class="hero">
      <div class="hero-text">
        <h1 style="font-size: 45px">Handcrafted Goods That Tell a Story</h1>
        <p style="color: black; font-size: 18px">
          Explore our curated collection of one-of-a-kind creations, lovingly
          made by skilled artisans from around the world. Elevate your space and
          style with the beauty of handmade craftsmanship.
        </p>
      </div>
      <div class="hero-image">
        <img src="source/pic2.png" alt="Handcrafted Pottery" />
      </div>
    </section>

    <section class="categories">
      <h2 style="font-size: 36px; color: black; margin-left: 70px">
        Browse By Category
      </h2>
      <div class="category-list">
        <div class="category">
          <p>Home Decor</p>
          <i class="fa fa-home"></i>
        </div>
        <div class="category active">
          <p>Kitchenware</p>
          <i class="fa fa-lemon"></i>
        </div>

        <div class="category">
          <p>Toys</p>
          <i class="fa fa-car"></i>
        </div>
        <div class="category">
          <p>Furniture</p>
          <i class="fas fa-couch"></i>
        </div>
        <div class="category">
          <p>Jewelry</p>
          <i class="fas fa-ring"></i>
        </div>
        <div class="category">
          <p>clothing</p>
          <i class="fas fa-shirt"></i>
        </div>
      </div>
    </section>
    <img
      src="source/Line.png"
      alt=""
      style="margin-left: 100px; margin-top: 100px; width: 1220px"
    />

    <section class="best-sellers">
      <div style="display: flex">
        <h2 style="font-size: 36px; color: black; margin-left: 70px">
          Best Selling Products
        </h2>
        <button
          style="
            background-color: #c02d0c;
            font-size: 16px;
            color: white;
            height: 56px;
            width: 159px;
            border-radius: 3px;
            margin-top: 18px;
            margin-left: 700px;
          "
        >
          view all
        </button>
      </div>

      <div class="products">
      <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <div class="product-card" onclick="viewProductDetails(<?php echo $row['product_id']; ?>)">
              <div class="product-image">
                <img 
                  src="<?php echo htmlspecialchars($row['image_path']); ?>" 
                  alt="<?php echo htmlspecialchars($row['product_name']); ?>" 
                  class="product-image-img"
                  onerror="this.onerror=null; this.src='source/pic1.png'; console.log('Image failed to load: <?php echo addslashes($row['image_path']); ?>');"
                />
                <div class="add-to-cart" onclick="addToCart(<?php echo $row['product_id']; ?>, event)">Add to Cart</div>
              </div>
              <div class="product-info">
                <h3 class="product-title"><?php echo htmlspecialchars($row['product_name']); ?></h3>
                <p class="product-price">
                  $<?php echo number_format($row['price'], 2); ?>
                </p>
                <p class="product-description"><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . (strlen($row['description']) > 100 ? '...' : ''); ?></p>
                <p class="product-category"><?php echo htmlspecialchars($row['category']); ?></p>
                <p class="product-quantity"><?php echo "Quantity: " . $row['quantity']; ?></p>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p style="margin-left: 70px; font-size: 18px; color: #444;">No products available in the store.</p>
        <?php endif; ?>
      </div>
    </section>

    <section class="best-sellers">
      <div style="display: flex">
        <h2 style="font-size: 36px; color: black; margin-left: 70px">
          View All Products
        </h2>
        <div style="margin-top: 28px; margin-left: 850px">
          <button style="border-radius: 90px; width: 46px; height: 46px">
            &lt;
          </button>
          <button style="border-radius: 90px; width: 46px; height: 46px">
            &gt;
          </button>
        </div>
      </div>

      <div class="products">
        <div class="product-card" onclick="viewProductDetails(1)">
          <div class="product-image">
            <img
              src="source/pic1.png"
              alt="Handmade Soap"
              class="product-image-img"
              onerror="this.onerror=null; this.src='source/default-product.png'; console.log('Image failed to load');"
            />
            <div class="add-to-cart" onclick="addToCart(1, event)">Add to Cart</div>
          </div>

          <div class="product-info">
            <h3 class="product-title">Handmade Soap</h3>
            <p class="product-price">
              $260 <span class="product-old-price">$360</span>
            </p>
            <p class="product-rating">★★★★★ (65)</p>
          </div>
        </div>
        <div class="product-card" onclick="viewProductDetails(2)">
          <div class="product-image">
            <img
              src="source/pic1.png"
              alt="Handmade Soap"
              class="product-image-img"
              onerror="this.onerror=null; this.src='source/default-product.png'; console.log('Image failed to load');"
            />
            <div class="add-to-cart" onclick="addToCart(2, event)">Add to Cart</div>
          </div>
          <div class="product-info">
            <h3 class="product-title">Handmade Soap</h3>
            <p class="product-price">
              $960 <span class="product-old-price">$1160</span>
            </p>
            <p class="product-rating">★★★★★ (65)</p>
          </div>
        </div>
        <div class="product-card" onclick="viewProductDetails(3)">
          <div class="product-image">
            <img
              src="source/pic1.png"
              alt="Handmade Soap"
              class="product-image-img"
              onerror="this.onerror=null; this.src='source/default-product.png'; console.log('Image failed to load');"
            />
            <div class="add-to-cart" onclick="addToCart(3, event)">Add to Cart</div>
          </div>
          <div class="product-info">
            <h3 class="product-title">Handmade Soap</h3>
            <p class="product-price">
              $160 <span class="product-old-price">$170</span>
            </p>
            <p class="product-rating">★★★★☆ (65)</p>
          </div>
        </div>
        <div class="product-card" onclick="viewProductDetails(4)">
          <div class="product-image">
            <img
              src="source/pic1.png"
              alt="Handmade Soap"
              class="product-image-img"
              onerror="this.onerror=null; this.src='source/default-product.png'; console.log('Image failed to load');"
            />
            <div class="add-to-cart" onclick="addToCart(4, event)">Add to Cart</div>
          </div>
          <div class="product-info">
            <h3 class="product-title">Handmade Soap</h3>
            <p class="product-price">$360</p>
            <p class="product-rating">★★★★★ (65)</p>
          </div>
        </div>
        <div class="product-card" onclick="viewProductDetails(5)">
          <div class="product-image">
            <img
              src="source/pic1.png"
              alt="Handmade Soap"
              class="product-image-img"
              onerror="this.onerror=null; this.src='source/default-product.png'; console.log('Image failed to load');"
            />
            <div class="add-to-cart" onclick="addToCart(5, event)">Add to Cart</div>
          </div>
          <div class="product-info">
            <h3 class="product-title">Handmade Soap</h3>
            <p class="product-price">$360</p>
            <p class="product-rating">★★★★★ (65)</p>
          </div>
        </div>
        <div class="product-card" onclick="viewProductDetails(6)">
          <div class="product-image">
            <img
              src="source/pic1.png"
              alt="Handmade Soap"
              class="product-image-img"
              onerror="this.onerror=null; this.src='source/default-product.png'; console.log('Image failed to load');"
            />
            <div class="add-to-cart" onclick="addToCart(6, event)">Add to Cart</div>
          </div>
          <div class="product-info">
            <h3 class="product-title">Handmade Soap</h3>
            <p class="product-price">$360</p>
            <p class="product-rating">★★★★★ (65)</p>
          </div>
        </div>
        <div class="product-card" onclick="viewProductDetails(7)">
          <div class="product-image">
            <img
              src="source/pic1.png"
              alt="Handmade Soap"
              class="product-image-img"
              onerror="this.onerror=null; this.src='source/default-product.png'; console.log('Image failed to load');"
            />
            <div class="add-to-cart" onclick="addToCart(7, event)">Add to Cart</div>
          </div>
          <div class="product-info">
            <h3 class="product-title">Handmade Soap</h3>
            <p class="product-price">$360</p>
            <p class="product-rating">★★★★★ (65)</p>
          </div>
        </div>
        <div class="product-card" onclick="viewProductDetails(8)">
          <div class="product-image">
            <img
              src="source/pic1.png"
              alt="Handmade Soap"
              class="product-image-img"
              onerror="this.onerror=null; this.src='source/default-product.png'; console.log('Image failed to load');"
            />
            <div class="add-to-cart" onclick="addToCart(8, event)">Add to Cart</div>
          </div>
          <div class="product-info">
            <h3 class="product-title">Handmade Soap</h3>
            <p class="product-price">$360</p>
            <p class="product-rating">★★★★★ (65)</p>
          </div>
        </div>
      </div>
    </section>
    <button
      style="
        background-color: #c02d0c;
        font-size: 16px;
        color: white;
        height: 56px;
        width: 159px;
        border-radius: 3px;
        margin-top: 18px;
        margin-left: 630px;
      "
    >
      View All Products
    </button>

    <img
      src="source/Frame.png"
      alt=""
      style="margin-left: 240px; margin-top: 100px; margin-bottom: 200px"
    />

    <footer>
      <div class="footer-content">
        <div class="exclusive">
          <h3>Exclusive</h3>
          <p>Subscribe</p>
          <p>Get 10% off your first order</p>
          <input type="email" placeholder="Enter your email" />
        </div>
        <div class="support">
          <h3>Support</h3>
          <p>111 Bijoy Sarani, Dhaka, DH 1515, Bangladesh.</p>
          <p>exclusive@gmail.com</p>
          <p>+88015-88888-9999</p>
        </div>
        <div class="account">
          <h3>Account</h3>
          <p>My Account</p>
          <p>Login / Register</p>
          <p>Cart</p>
          <p>Wishlist</p>
          <p>Shop</p>
        </div>
        <div class="quick-links">
          <h3>Quick Link</h3>
          <p>Privacy Policy</p>
          <p>Terms Of Use</p>
          <p>FAQ</p>
          <p>Contact</p>
        </div>
        <div class="download-app">
          <h3>Download App</h3>
          <p style="font-size: 12px">Save $3 with App New User Only</p>
          <div>
            <img src="Frame 719.png" alt="" />
          </div>
          <div>
            <img src="Frame 741.png" alt="" />
          </div>
        </div>
      </div>
      <p class="copyright">©️ Copyright Rimel 2022. All rights reserved</p>
    </footer>

    <!-- JavaScript functions for handling product navigation and cart functionality -->
    <script>
      // Function to navigate to product details page
      function viewProductDetails(productId) {
        window.location.href = `productDetails.html?id=${productId}`;
      }
      
      // Function to add to cart without navigating to product details
      function addToCart(productId, event) {
        // Stop the click event from bubbling up to the parent
        event.stopPropagation();
        
        // Here you would add your cart logic
        console.log(`Adding product ${productId} to cart`);
        
        // For demonstration purposes, show an alert
        alert(`Product ${productId} added to cart!`);
      }
      
      // Debug image loading
      document.addEventListener('DOMContentLoaded', function() {
        console.log('Document loaded, checking images...');
        const images = document.querySelectorAll('.product-image-img');
        images.forEach((img, index) => {
          console.log(`Image ${index+1} src: ${img.src}`);
        });
      });
    </script>
  </body>
</html>
<?php
// Close the database connection
$conn->close();
?>
