CREATE DATABASE reviews_db;

USE reviews_db;

CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255),
    review_text TEXT
);
