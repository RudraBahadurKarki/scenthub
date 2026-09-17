CREATE DATABASE  scenthub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE scenthub_db;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS contact_messages, reviews, payments, order_items, orders, cart, products, fragrance_families, brands, categories, users;
SET FOREIGN_KEY_CHECKS=1;


CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  phone VARCHAR(30),
  address TEXT,
  password VARCHAR(255) NOT NULL,
  status ENUM('active','blocked') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE brands (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE fragrance_families (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  brand_id INT NOT NULL,
  category_id INT NOT NULL,
  family_id INT NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  stock INT DEFAULT 0,
  image VARCHAR(255),
  is_featured TINYINT(1) DEFAULT 0,
  is_bestseller TINYINT(1) DEFAULT 0,
  is_new TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
  FOREIGN KEY (family_id) REFERENCES fragrance_families(id) ON DELETE CASCADE
);

CREATE TABLE cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_cart (user_id, product_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  customer_name VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  address TEXT NOT NULL,
  city VARCHAR(80) NOT NULL,
  payment_method ENUM('Cash on Delivery','eSewa','Khalti') NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  product_name VARCHAR(160) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  method VARCHAR(50) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  status ENUM('Pending','Paid','Failed') DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL,
  subject VARCHAR(160) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admins (name,email,password) VALUES
('ScentHub Admin','admin@scenthub.com','240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9');

INSERT INTO users (name,email,phone,address,password) VALUES
('Demo User','user@scenthub.com','9800000000','Kathmandu, Nepal','e606e38b0d8c19b24cf0ee3808183162ea7cd63ff7912dbb22b5e803286b4446');

INSERT INTO categories (name) VALUES
('Men''s Perfumes'),('Women''s Perfumes'),('Unisex Perfumes'),('Body Mist'),('Gift Sets'),('Mini Perfumes');

INSERT INTO brands (name) VALUES
('Dior'),('Chanel'),('Gucci'),('Versace'),('Davidoff'),('Jaguar'),('Calvin Klein'),('Armani');

INSERT INTO fragrance_families (name) VALUES
('Woody'),('Floral'),('Citrus'),('Fresh'),('Musky'),('Sweet'),('Spicy');

INSERT INTO products (name,brand_id,category_id,family_id,description,price,stock,image,is_featured,is_bestseller,is_new) VALUES
('Dior Sauvage Elixir',1,1,7,'Bold spicy notes with lavender, woods, and a rich masculine finish.',18500,12,NULL,1,1,0),
('Chanel Coco Mademoiselle',2,2,2,'Elegant rose, jasmine, patchouli, and amber for a timeless luxury aura.',22000,10,NULL,1,1,0),
('Gucci Bloom Eau de Parfum',3,2,2,'White floral bouquet with tuberose and jasmine for a graceful signature.',14500,18,NULL,1,0,1),
('Versace Eros Flame',4,1,1,'Woody citrus spice blended with vanilla and tonka warmth.',12500,14,NULL,0,1,0),
('Davidoff Cool Water',5,1,4,'Fresh aquatic perfume with mint, lavender, and clean marine energy.',7800,25,NULL,1,1,0),
('Jaguar Classic Black',6,1,6,'Sweet oriental amber and citrus with a confident evening character.',6200,22,NULL,0,0,1),
('CK One',7,3,3,'Clean unisex citrus tea fragrance with effortless daily freshness.',8500,30,NULL,1,0,0),
('Armani Si Passione',8,2,6,'Sparkling pear, rose, vanilla, and cedar with feminine warmth.',16500,11,NULL,0,1,1),
('Dior J’adore',1,2,2,'Radiant floral perfume with ylang-ylang, rose, and jasmine petals.',21000,9,NULL,1,0,1),
('Chanel Bleu de Chanel',2,1,1,'Refined woody aromatic scent with citrus, incense, and sandalwood.',20500,13,NULL,1,1,0),
('Gucci Guilty Pour Homme',3,1,7,'Modern aromatic spice with lemon, lavender, and patchouli depth.',13800,15,NULL,0,0,1),
('Versace Bright Crystal',4,2,4,'Fresh pomegranate, peony, magnolia, and musk in a luminous profile.',11800,20,NULL,1,0,0),
('Davidoff Cool Water Woman',5,2,4,'Aquatic floral freshness with melon, lily, and soft woods.',7600,21,NULL,0,0,1),
('Jaguar Classic Gold',6,1,6,'Apple, lime, teak wood, and vanilla for a polished sweet signature.',6500,19,NULL,0,0,0),
('Calvin Klein Eternity Gift Set',7,5,2,'Romantic floral gift set with perfume and travel-size essentials.',13500,8,NULL,1,0,1),
('Armani Acqua di Gio Mini',8,6,3,'Mini citrus marine luxury perfume for travel and gifting.',5400,28,NULL,0,0,1),
('ScentHub Velvet Body Mist',4,4,6,'Soft sweet body mist with vanilla, berries, and gentle musk.',3200,40,NULL,0,1,1),
('ScentHub Oud Royale',1,3,1,'Unisex oud, amber, saffron, and smoky woods for special occasions.',24000,7,NULL,1,1,1);


INSERT INTO contact_messages (name,email,subject,message) VALUES
('Sample Visitor','visitor@example.com','Wholesale inquiry','Do you provide gift packaging for bulk orders?');
