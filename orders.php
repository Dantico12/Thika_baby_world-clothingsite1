<?php
// Include the database connection file
require 'db_connection.php';

// Fetch orders along with client names and product details
$sql_orders = "
    SELECT o.sale_id, id, o.total_amount, o.sale_date, u.username, u.email, u.phone, p.product_id, p.name, p.image 
    FROM orders o
    JOIN users u ON id = u.id
    JOIN orders op ON o.sale_id = op.sale_id
    JOIN products p ON id = id
";
$result_orders = $conn->query($sql_orders);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            background-color: #f4f4f4;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
        }

        nav {
            width: 200px;
            background-color: #333;
            min-height: 100vh; /* Full height of the viewport */
            position: fixed; /* Keep the sidebar fixed */
            padding-top: 20px;
        }

        nav ul {
            list-style-type: none;
            padding: 0;
        }

        nav ul li {
            padding: 10px;
            text-align: center;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            display: block;
        }

        nav ul li a:hover {
            background-color: #575757;
        }

        .content {
            margin-left: 220px; /* Space for the sidebar */
            padding: 20px;
            flex: 1; /* Take up remaining space */
        }

        h1 {
            text-align: center;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .add-order-btn {
            display: block;
            width: 200px;
            padding: 10px;
            margin: 20px auto;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
        }

        .add-order-btn:hover {
            background-color: #45a049;
        }

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }

        .action-btns {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }

        .edit-btn {
            background-color: #2196F3; /* Blue */
        }

        .delete-btn {
            background-color: #f44336; /* Red */
        }

        .contact-btn {
            background-color: #25D366; /* WhatsApp Green */
        }
    </style>
</head>
<body>
<header>
    <h2>Admin Panel</h2>
</header>

<nav>
    <ul>
        <li><a href="admin_dashboard.html">Dashboard</a></li>
        <li><a href="add_product.php">Add Product</a></li>
        <li><a href="users.php">View Users</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="inventory.php">Inventory</a></li>
        <li><a href="login.php">Logout</a></li>
    </ul>
</nav>

<div class="content">
    <h1>Add Order</h1>
    
    <h2>Orders and Products</h2>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Client Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Total Amount</th>
            <th>Order Date</th>
            <th>Product Name</th>
            <th>Product Image</th>
            <th>Actions</th>
        </tr>
        <?php while ($order = $result_orders->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                <td><?php echo htmlspecialchars($order['username']); ?></td>
                <td><?php echo htmlspecialchars($order['email']); ?></td>
                <td><?php echo htmlspecialchars($order['phone']); ?></td>
                <td><?php echo htmlspecialchars($order['total_amount']); ?></td>
                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                <td><img src="<?php echo htmlspecialchars($order['product_image']); ?>" alt="Product Image" class="product-image"></td>
                <td class="action-btns">
                    <button class="action-btn edit-btn" onclick="window.location.href='edit_order.php?id=<?php echo $order['order_id']; ?>'">Edit</button>
                    <button class="action-btn delete-btn" onclick="confirmDelete('<?php echo $order['order_id']; ?>')">Delete</button>
                    <button class="action-btn contact-btn" onclick="window.open('https://wa.me/<?php echo htmlspecialchars($order['phone']); ?>', '_blank')">Contact</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <button class="add-order-btn" onclick="window.location.href='create_order.php'">Create New Order</button>
</div>

<script>
function confirmDelete(orderId) {
    if (confirm('Are you sure you want to delete this order?')) {
        window.location.href = 'delete_order.php?id=' + orderId;
    }
}
</script>
</body>
</html>
