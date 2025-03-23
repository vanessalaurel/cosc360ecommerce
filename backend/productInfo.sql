CREATE DATABASE productInfo_db;

-- Use the database
USE productInfo_db;

-- Create table for products
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                       -- To link products to users who listed them
    product_name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    image_path VARCHAR(255),                    -- Path to the uploaded image
    date_listed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Add a foreign key if you want to link to your users table
    FOREIGN KEY (user_id) REFERENCES userInfo_db.users(user_id)
);

CREATE TABLE product_feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5), -- Rating from 1-5 stars
    comment TEXT,
    feedback_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Add foreign keys to link feedback to products and users
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES userInfo_db.users(user_id),
    
    -- Ensure a user can only leave one feedback per product
    CONSTRAINT unique_feedback UNIQUE (product_id, user_id)
);