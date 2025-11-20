<?php
session_start();
require_once 'db.php';

// Fetch menu items by category
$menuItems = [];
$categories = ['Signature Kacchi', 'Weekly Special', 'Sides & Drinks'];

foreach ($categories as $category) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE category = ? AND is_available = TRUE");
    $stmt->execute([$category]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($items)) {
        $menuItems[$category] = $items;
    }
}
    
// Get current page for active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KACCHI KHACCHI - Narayanganj's Favorite Kacchi</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@400;500;600&family=Poppins:wght@300;400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        /* Enhanced image handling */
        .hero-image {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
        }
        
        .hero-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            animation: floatAnimation 6s ease-in-out infinite;
            max-width: 600px;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));
        }
        
        /* Floating animation for hero image */
        @keyframes floatAnimation {
            0%, 100% {
                transform: translateY(0px) scale(1);
                opacity: 0.9;
            }
            50% {
                transform: translateY(-15px) scale(1.02);
                opacity: 1;
            }
        }
        
        /* Fade in animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .hero-image img {
            animation: floatAnimation 6s ease-in-out infinite, fadeIn 1.5s ease-out;
        }
        
        .item-image {
            height: 220px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.3s ease;
        }
        
        .menu-item:hover .item-image img {
            transform: scale(1.05);
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Hero section improvements */
        .hero-content {
            align-items: center;
        }

        .hero-text {
            animation: fadeIn 1s ease-out 0.2s both;
        }
    </style>
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
                        <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                        <li><a href="#menu">Menu</a></li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <li><a href="orders.php" class="<?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">My Orders</a></li>
                        <?php endif; ?>
                        <li>
                            <a href="#" id="cartToggle" class="cart-icon">
                                Cart <span class="cart-count">0</span>
                            </a>
                        </li>
                        <div class="user-auth">
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                                <a href="logout.php" class="btn btn-primary">Logout</a>
                            <?php else: ?>
                                <a href="login.php" class="login-btn">Login</a>
                                <a href="register.php" class="register-btn">Register</a>
                            <?php endif; ?>
                        </div>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

<!-- Hero Section -->
<section id="home" class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h2>Authentic Kacchi,<br>Royal Taste</h2>
                <p>Order now and get it delivered to your doorstep in Narayanganj</p>
                <a href="#menu" class="btn btn-primary">Order Now</a>
            </div>
            <div class="hero-image">
        
                <img src="assets/images/KK_Logo.png" 
                     alt="Delicious Kacchi Biryani"
                     loading="eager"
                     onerror="handleHeroImageError(this)">
            </div>
        </div>
    </div>
</section>

    <!-- Menu Section -->
    <section id="menu" class="menu">
        <div class="container">
            <h2 class="section-title">Our Menu</h2>
            
            <?php foreach($menuItems as $category => $items): ?>
                <?php if(!empty($items)): ?>
                    <div class="menu-category">
                        <h3 class="category-title"><?php echo $category; ?></h3>
                        <div class="menu-items">
                            <?php foreach($items as $item): ?>
                                <div class="menu-item" data-id="<?php echo $item['id']; ?>" 
                                     data-name="<?php echo htmlspecialchars($item['name']); ?>" 
                                     data-price="<?php echo $item['price']; ?>">
                                    <div class="item-image">
                                        <?php if(!empty($item['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                 loading="lazy"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <?php endif; ?>
                                        <div class="image-placeholder" style="<?php echo !empty($item['image_url']) ? 'display: none;' : 'display: flex;'; ?>">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </div>
                                    </div>
                                    <div class="item-details">
                                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                                        <div class="item-footer">
                                            <div class="price"><?php echo number_format($item['price'], 2); ?> TK</div>
                                            <button class="add-to-cart">Add to Cart</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
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

    <!-- Checkout Modal -->
    <div id="checkoutModal" class="modal">
        <div class="modal-content">
            <button class="close-modal close-checkout">&times;</button>
            <h2>Complete Your Order</h2>
            <form id="orderForm">
                <div class="form-group">
                    <label for="name">Your Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required
                           value="<?php echo isset($_SESSION['user_phone']) ? htmlspecialchars($_SESSION['user_phone']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="address">Delivery Address *</label>
                    <textarea id="address" name="address" required><?php echo isset($_SESSION['user_address']) ? htmlspecialchars($_SESSION['user_address']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="instructions">Special Instructions (Optional)</label>
                    <textarea id="instructions" name="instructions"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Place Order 🎉</button>
            </form>
        </div>
    </div>

    <!-- Order Confirmation -->
    <div id="orderConfirmation" class="modal">
        <div class="modal-content">
            <div class="confirmation-content">
                <div class="checkmark">✓</div>
                <h2>Order Confirmed! 🎉</h2>
                <p>Your delicious Kacchi is being prepared with love!</p>
                <p>We'll send you an SMS with delivery details</p>
                <div class="order-number">Order #<span id="orderNumber">12345</span></div>
                <button id="newOrder" class="btn btn-primary">Place Another Order</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>KACCHI KHACCHI</h3>
                    <p>Narayanganj's Favorite Kacchi Experience since 2025</p>
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