<?php
session_start();
require_once 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user's all orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$userOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current page for active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - KACCHI KHACCHI</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@400;500;600&family=Poppins:wght@300;400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>KACCHI KHACCHI</h1>
                    <p>Narayanganj's Favorite Kacchi Experience!</p>
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#menu">Menu</a></li>
                        <li><a href="orders.php" class="active">My Orders</a></li>
                        <li>
                            <a href="#" id="cartToggle" class="cart-icon">
                                Cart <span class="cart-count">0</span>
                            </a>
                        </li>
                        <div class="user-auth">
                            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            <a href="logout.php" class="btn btn-primary">Logout</a>
                        </div>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Orders Section -->
    <section class="orders-section">
        <div class="container">
            <div class="admin-table">
                <div class="table-header">
                    <h2>My Orders</h2>
                    <span class="order-count"><?php echo count($userOrders); ?> order(s)</span>
                </div>
                
                <?php if(empty($userOrders)): ?>
                    <div class="empty-orders">
                        <h3>No orders yet</h3>
                        <p>You haven't placed any orders yet.</p>
                        <a href="index.php#menu" class="btn btn-primary">Browse Menu</a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date & Time</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($userOrders as $order): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo number_format($order['total_amount'], 2); ?> TK</td>
                                    <td>
                                        <span class="status-pill status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="order-details-popup">
                                            <strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?><br>
                                            <strong>Address:</strong> <?php echo htmlspecialchars($order['customer_address']); ?>
                                            <?php if(!empty($order['special_instructions'])): ?>
                                                <br><strong>Instructions:</strong> <?php echo htmlspecialchars($order['special_instructions']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Cart Overlay -->
    <div id="cartOverlay" class="cart-overlay">
        <div class="cart">
            <button class="close-cart">&times;</button>
            <h2>Your Order</h2>
            <div id="cartContent" class="cart-content">
                <p class="empty-cart">Your cart is empty</p>
            </div>
            <div class="cart-total">
                <div class="total-item">
                    <span>Subtotal:</span>
                    <span id="subtotal">0 TK</span>
                </div>
                <div class="total-item">
                    <span>Delivery Fee:</span>
                    <span>60 TK</span>
                </div>
                <div class="total-item grand-total">
                    <span>Total:</span>
                    <span id="grandTotal">60 TK</span>
                </div>
            </div>
            <button id="checkoutBtn" class="btn checkout-btn" disabled>Proceed to Checkout</button>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>KACCHI KHACCHI</h3>
                    <p>Narayanganj's Favorite Kacchi</p>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p>Phone: +880 1609-090909</p>
                    <p>Email: info@kacchikhacchi.com</p>
                </div>
                <div class="footer-section">
                    <h3>Delivery Area</h3>
                    <p>Currently serving only in Narayanganj</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 KACCHI KHACCHI</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>