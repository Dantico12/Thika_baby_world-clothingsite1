<?php
// Include the database connection file
require 'db_connection.php';

// Handle user deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $delete_sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: users.php");
    exit;
}

// Fetch users from the database
$sql = "SELECT id, username, email, phone, created_at FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
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
        }
        h1 {
            margin-bottom: 30px;
            color: #4CAF50;
            text-align: center;
            font-size: 2.5em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #4CAF50;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #3a3a3a;
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
        <h1>View Users</h1>
        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" style="color: #4CAF50;">Edit</a>
                                <a href="?action=delete&id=<?php echo $user['id']; ?>" style="color: red;" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
