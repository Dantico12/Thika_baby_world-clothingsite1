<?php
session_start();
require 'db_connection.php';

// Initialize variables
$productId = null;
$name = '';
$category = '';
$description = '';
$price = '';
$quantity = '';
$image = '';
$message = '';

// Check if an ID is provided for editing
if (isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];

    $sql = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $name = $product['name'];
        $category = $product['category'];
        $description = $product['description'];
        $price = $product['price'];
        $quantity = $product['quantity'];
        $image = $product['image'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    } else {
        if (!$productId) {
            $image = '';
        } else {
            $image = $product['image'];
        }
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        if ($productId) {
            // Update existing product
            $sql = "UPDATE products SET name=?, category=?, description=?, price=?, quantity=?, image=? WHERE product_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssdssi", $name, $category, $description, $price, $quantity, $image, $productId);
            $stmt->execute();

            // Record inventory movement if quantity changed
            if ($quantity != $product['quantity']) {
                $quantity_difference = $quantity - $product['quantity'];
                $movement_type = $quantity_difference > 0 ? 'in' : 'out';
                $movement_quantity = abs($quantity_difference);
                $reason = 'Quantity adjustment';

                $stmt = $conn->prepare("INSERT INTO inventory_movements (product_id, movement_type, quantity, reason) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isis", $productId, $movement_type, $movement_quantity, $reason);
                $stmt->execute();
            }
        } else {
            // Add new product
            $sql = "INSERT INTO products (name, category, description, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssdss", $name, $category, $description, $price, $quantity, $image);
            $stmt->execute();

            $product_id = $conn->insert_id;

            // Record initial inventory movement
            $movement_type = 'in';
            $reason = 'Initial stock';
            $stmt = $conn->prepare("INSERT INTO inventory_movements (product_id, movement_type, quantity, reason) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isis", $product_id, $movement_type, $quantity, $reason);
            $stmt->execute();
        }

        // Commit transaction
        $conn->commit();

        // Redirect with success message
        header("Location: view_product.php?message=Product " . ($productId ? 'updated' : 'added') . " successfully!");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $productId ? 'Edit' : 'Add'; ?> Product</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
            background-color: #1a1a1a;
            color: #fff;
        }

        .main-container {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 200px;
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 20px 0;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            padding: 10px 20px;
            margin-bottom: 10px;
        }

        .sidebar ul li a {
            color: #ecf0f1;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .sidebar ul li a:hover {
            color: #3498db;
        }

        .container {
            flex-grow: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            margin-bottom: 30px;
            color: #4CAF50;
            text-align: center;
            font-size: 2.5em;
        }

        .message {
            background-color: #2c2c2c;
            color: #4CAF50;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #4CAF50;
            border-radius: 5px;
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        form {
            background-color: #2c2c2c;
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #4CAF50;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        input[type="file"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #4CAF50;
            border-radius: 5px;
            background-color: #1a1a1a;
            color: #fff;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="number"]:focus,
        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            border-color: #3498db;
            outline: none;
        }

        textarea {
            resize: vertical;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.1s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }
    </style>
</head>

<body>
    <div class="main-container">
        <aside class="sidebar">
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="add_product.php">Add Product</a></li>
                    <li><a href="users.php">View Users</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="inventory.php">Inventory</a></li>
                    <li><a href="login.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <div class="container">
            <h1><?php echo $productId ? 'Edit' : 'Add'; ?> Product</h1>
            <?php if ($message): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Product Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="" disabled <?php echo $category == '' ? 'selected' : ''; ?>>Select a category</option>
                        <option value="Boys" <?php echo $category == 'Boys' ? 'selected' : ''; ?>>Boys</option>
                        <option value="Girls" <?php echo $category == 'Girls' ? 'selected' : ''; ?>>Girls</option>
                        <option value="Accessories" <?php echo $category == 'Accessories' ? 'selected' : ''; ?>>Accessories</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Price:</label>
                    <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" required  placeholder="Enter price">
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($quantity); ?>" required  placeholder="Enter quantity">
                </div>
                <div class="form-group">
                    <label for="image">Product Image:</label>
                    <input type="file" id="image" name="image">
                </div>
                <div class="button-group">
                    <a href="view_product.php" class="btn">Cancel</a>
                    <button type="submit" class="btn"><?php echo $productId ? 'Update' : 'Add'; ?> Product</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>