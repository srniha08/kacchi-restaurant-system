<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];

    $valid_statuses = ['pending', 'confirmed', 'preparing', 'out-for-delivery', 'delivered', 'cancelled'];
    if (in_array($new_status, $valid_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $order_id])) {
            $success = "Order status updated successfully!";
        } else {
            $error = "Failed to update order status.";
        }
    } else {
        $error = "Invalid status selected.";
    }
}

// Get statistics
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$total_revenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'delivered'")->fetchColumn();
$menu_items_count = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();

// Get recent orders with customer details (even if user is deleted)
$recent_orders = $pdo->query("
    SELECT 
        o.*,
        COALESCE(u.name, 'Deleted User') AS customer_name,
        COALESCE(u.email, 'N/A') AS customer_email,
        COALESCE(u.phone, 'N/A') AS customer_phone
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Get all menu items
$all_menu_items = $pdo->query("
    SELECT * FROM menu_items 
    ORDER BY 
        FIELD(category, 'Signature Kacchi', 'Weekly Special', 'Sides & Drinks'),
        name
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KACCHI KHACCHI</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@400;500;600&family=Poppins:wght@300;400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-preparing { background: #d1ecf1; color: #0c5460; }
        .status-out-for-delivery { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .success-message, .error-message {
            padding: 15px; margin: 20px 0; border-radius: 8px; font-weight: 500;
        }
        .success-message { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .status-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            font-size: 0.9rem;
        }
        .status-select:focus { outline: none; border-color: #7a4e2a; }
    </style>
</head>
<body class="admin-body">
    <header class="admin-header">
        <div class="container">
            <nav class="admin-nav">
                <div class="logo">
                    <h1>KACCHI KHACCHI - Admin</h1>
                </div>
                <ul>
                    <li><a href="admin_dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="add_item.php">Add Item</a></li>
                    <li><a href="admin_logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="admin-main">
        <div class="container">

            <?php if (isset($success)): ?>
                <div class="success-message">
                    <strong>Success:</strong> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($total_orders); ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($pending_orders); ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($total_revenue, 2); ?> TK</div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $menu_items_count; ?></div>
                    <div class="stat-label">Menu Items</div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="admin-table">
                <div class="table-header">
                    <h2>Recent Orders</h2>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date & Time</th>
                                <th>Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_orders)): ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; padding: 40px; color: #999;">
                                        No orders found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($recent_orders as $order): ?>
                                    <tr>
                                        <td><strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                                        <td><strong><?php echo number_format($order['total_amount'], 2); ?> TK</strong></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucwords(str_replace('-', ' ', $order['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y <br> g:i A', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" class="status-update-form" style="margin:0;">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <select name="status" class="status-select" onchange="this.form.submit()">
                                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                    <option value="preparing" <?php echo $order['status'] == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                                    <option value="out-for-delivery" <?php echo $order['status'] == 'out-for-delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                                    <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Menu Items Management -->
            <div class="admin-table">
                <div class="table-header">
                    <h2>Menu Items Management</h2>
                    <a href="add_item.php" class="btn btn-primary">Add New Item</a>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Availability</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($all_menu_items as $item): ?>
                                <tr>
                                    <td>
                                        <?php if(!empty($item['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 2px solid #f0f0f0;">
                                        <?php else: ?>
                                            <div style="width:60px;height:60px;background:#eee;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#999;font-size:0.8rem;">
                                                No Image
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                                    <td><strong><?php echo number_format($item['price'], 2); ?> TK</strong></td>
                                    <td>
                                        <?php if(!empty($item['is_available'])): ?>
                                            <span style="color:#155724; font-weight:600;">Available</span>
                                        <?php else: ?>
                                            <span style="color:#721c24; font-weight:600;">Unavailable</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="delete_item.php?id=<?php echo $item['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete \'<?php echo addslashes($item['name']); ?>\'? This cannot be undone.')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>