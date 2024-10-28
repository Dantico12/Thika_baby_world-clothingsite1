<?php
session_start();
require 'db_connection.php';

// Your existing PHP code for database queries and form processing remains the same
$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM orders s WHERE s.product_id = p.product_id) as times_sold,
          (SELECT SUM(quantity) FROM orders s WHERE s.product_id = p.product_id) as total_sold
          FROM products p";
$result = $conn->query($query);

// Process sale if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['make_sale'])) {
    // Your existing sale processing code remains the same
}

$movements_query = "SELECT m.*, p.name, 
                   CASE WHEN m.movement_type = 'in' THEN 'Stock Added' ELSE 'Stock Removed' END as movement_description
                   FROM inventory_movements m 
                   JOIN products p ON m.product_id = p.product_id 
                   ORDER BY m.movement_date DESC 
                   LIMIT 10";
$movements = $conn->query($movements_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            min-height: 100vh;
        }

        .wrapper {
            display: grid;
            grid-template-columns: 200px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background-color: #2c3e50;
            padding: 20px;
            color: #ecf0f1;
        }

        .sidebar nav ul {
            list-style: none;
            padding: 0;
        }

        .sidebar nav ul li {
            margin-bottom: 10px;
        }

        .sidebar nav ul li a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 16px;
            display: block;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .sidebar nav ul li a:hover {
            background-color: #34495e;
        }

        .container {
            padding: 20px;
            background-color: white;
            overflow: hidden;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 24px;
        }

        h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .table-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .inventory-list, .inventory-movements {
            flex: 1;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            background-color: #fff;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #34495e;
            color: white;
            font-weight: normal;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .sale-form {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .sale-form input[type="number"] {
            width: 60px;
            padding: 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        button:hover {
            background-color: #45a049;
        }

        @media (max-width: 1200px) {
            .table-container {
                flex-direction: column;
            }

            .inventory-list, .inventory-movements {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .wrapper {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }

            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
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
            <h2>Inventory Management</h2>
            
            <div class="table-container">
                <div class="inventory-list">
                    <h3>Current Stock</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                                <th>Sold</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo $row['times_sold'] ?? 0; ?></td>
                                <td><?php echo $row['total_sold'] ?? 0; ?></td>
                                <td>
                                    <form method="POST" action="" class="sale-form">
                                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                        <input type="number" name="quantity" min="1" max="<?php echo $row['quantity']; ?>" required>
                                        <button type="submit" name="make_sale">Sale</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="inventory-movements">
                    <h3>Recent Movements</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($movement = $movements->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('Y-m-d H:i', strtotime($movement['movement_date'])); ?></td>
                                <td><?php echo htmlspecialchars($movement['name']); ?></td>
                                <td><?php echo $movement['movement_description']; ?></td>
                                <td><?php echo $movement['quantity']; ?></td>
                                <td><?php echo htmlspecialchars($movement['reason']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>