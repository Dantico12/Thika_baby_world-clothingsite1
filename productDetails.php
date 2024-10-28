<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
try {
    require 'db_connection.php'; // Ensure this connects to your database correctly
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize variables
$productId = null;
$product = null;
$error = null;

// Validate and get product ID
if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $productId = (int)$_GET['product_id'];

    // Fetch product details
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
        } else {
            $error = "Product not found";
        }
        $stmt->close();
    } catch (Exception $e) {
        $error = "Error fetching product details: " . $e->getMessage();
    }
} else {
    $error = "Invalid product ID";
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }

    $userId = $_SESSION['user_id']; // Get the logged-in user ID
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

    if ($quantity <= 0 || $quantity > 99) {
        $error = "Please enter a valid quantity (1-99)";
    } else {
        // Calculate total price (quantity * product price)
        $totalPrice = $quantity * $product['price'];

        try {
            // Check if the product is already in the cart for the user
            $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $userId, $productId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Product already in the cart, update the quantity and total price
                $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ?, total_price = total_price + ? WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("idii", $quantity, $totalPrice, $userId, $productId);
            } else {
                // Product not in the cart, insert it with the correct total price
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $userId, $productId, $quantity, $totalPrice);
            }

            $stmt->execute();
            $stmt->close();

            // Redirect to cart.php
            header("Location: cart.php");
            exit();
        } catch (Exception $e) {
            $error = "Error adding product to cart: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? htmlspecialchars($product['name']) : 'Product Details'; ?> - Thika Baby World</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <style>
        /* Product Details Specific Styles */
        .product-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .product-details {
            display: flex;
            gap: 2rem;
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            flex: 1;
            min-width: 300px;
            max-width: 500px; /* Limit maximum width */
            aspect-ratio: 1; /* Force square aspect ratio */
            position: relative; /* For absolute positioning of image */
            background-color: #f8f8f8; /* Light background for empty space */
            border-radius: 8px;
            overflow: hidden;
        }

        .product-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain; /* Ensure entire image is visible */
            padding: 1rem; /* Add some padding around the image */
            background-color: white;
            transition: transform 0.3s ease; /* Smooth zoom effect */
        }

        .product-image img:hover {
            transform: scale(1.05); /* Slight zoom on hover */
        }

        .product-info {
            flex: 1;
            min-width: 300px; /* Ensure info section doesn't get too narrow */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .product-details {
                flex-direction: column;
                align-items: center;
            }

            .product-image {
                width: 100%;
                max-width: 400px; /* Smaller on mobile */
            }

            .product-info {
                width: 100%;
            }
        }

        .product-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .product-category {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .product-description {
            color: #444;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .product-price {
            font-size: 1.5rem;
            color: #e44d26;
            font-weight: bold;
            margin-bottom: 2rem;
        }

        .quantity-input {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .quantity-input input {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .add-to-cart-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background-color 0.3s;
        }

        .add-to-cart-btn:hover {
            background-color: #45a049;
        }

        .message {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .error {
            background-color: #ffe6e6;
            color: #d63031;
            border: 1px solid #ff7675;
        }

        .success {
            background-color: #e6ffe6;
            color: #27ae60;
            border: 1px solid #2ecc71;
        }

        /* Navigation Styles */
        .breadcrumb {
            padding: 1rem 0;
            color: #666;
        }

        .breadcrumb a {
            color: #4CAF50;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="container d-flex">
            <p>Order online or call us: +254 719 415 624</p>
            <ul class="d-flex">
                <li><a href="about.php">About Us</a></li>
                <li><a href="faq.php">FAQ</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Navigation -->
    <div class="navigation">
        <div class="nav-center container d-flex">
            <a href="index.php" class="logo">
                <h2>Thika Baby World</h2>
            </a>
            <ul class="nav-list d-flex">
                <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="products.php" class="nav-link">Shop</a></li>
                <li class="nav-item"><a href="about.php" class="nav-link">About</a></li>
                <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
                <li class="nav-item">
                    <a href="cart.php" class="nav-link">
                        <i class='bx bx-cart'></i> Cart
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="breadcrumb container">
        <a href="index.php">Home</a> &gt; 
        <a href="products.php">Shop</a> &gt; 
        <span><?php echo $product ? htmlspecialchars($product['name']) : 'Product Details'; ?></span>
    </div>

    <div class="product-container">
        <div class="product-details">
            <div class="product-image">
                <?php if ($product && !empty($product['image'])): ?>
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" />
                <?php else: ?>
                    <img src="placeholder.jpg" alt="Image not available" />
                <?php endif; ?>
            </div>
            <div class="product-info">
                <h2 class="product-title"><?php echo $product ? htmlspecialchars($product['name']) : 'N/A'; ?></h2>
                <p class="product-category"><?php echo $product ? htmlspecialchars($product['category']) : 'N/A'; ?></p>
                <p class="product-description"><?php echo $product ? htmlspecialchars($product['description']) : 'No description available'; ?></p>
                <p class="product-price">KSh <?php echo $product ? htmlspecialchars(number_format($product['price'], 2)) : '0.00'; ?></p>

                <?php if (!empty($error)): ?>
                    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="quantity-input">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" min="1" max="99" value="1" required>
                    </div>
                    <button type="submit" name="add_to_cart" class="add-to-cart-btn">Add to Cart</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container d-flex">
            <div class="footer-left">
                <p>&copy; 2024 Thika Baby World. All Rights Reserved.</p>
            </div>
            <div class="footer-right">
                <p>Follow us on:</p>
                <a href="#"><i class='bx bxl-facebook'></i></a>
                <a href="#"><i class='bx bxl-twitter'></i></a>
                <a href="#"><i class='bx bxl-instagram'></i></a>
            </div>
        </div>
    </footer>
</body>
</html>
