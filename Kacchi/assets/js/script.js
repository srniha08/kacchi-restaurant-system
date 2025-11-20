// Enhanced cart functionality with better error handling
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    updateCartDisplay();

    // Cart toggle functionality
    const cartToggle = document.getElementById('cartToggle');
    const cartOverlay = document.getElementById('cartOverlay');
    const closeCart = document.querySelector('.close-cart');

    if (cartToggle && cartOverlay) {
        cartToggle.addEventListener('click', function(e) {
            e.preventDefault();
            cartOverlay.classList.add('active');
            document.body.classList.add('modal-open');
        });

        if (closeCart) {
            closeCart.addEventListener('click', function() {
                cartOverlay.classList.remove('active');
                document.body.classList.remove('modal-open');
            });
        }
    }

    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const menuItem = this.closest('.menu-item');
            if (!menuItem) return;
            
            const itemId = menuItem.dataset.id;
            const itemName = menuItem.dataset.name;
            const itemPrice = parseFloat(menuItem.dataset.price);

            if (itemId && itemName && itemPrice) {
                addToCart(itemId, itemName, itemPrice);
                showNotification('Item added to cart!', 'success');
            }
        });
    });

    function addToCart(id, name, price) {
        const existingItem = cart.find(item => item.id === id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                id: id,
                name: name,
                price: price,
                quantity: 1
            });
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartDisplay();
    }

    function updateCartDisplay() {
        const cartCount = document.querySelector('.cart-count');
        const cartContent = document.getElementById('cartContent');
        const subtotalElement = document.getElementById('subtotal');
        const grandTotalElement = document.getElementById('grandTotal');
        const checkoutBtn = document.getElementById('checkoutBtn');

        // Update cart count
        if (cartCount) {
            const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
            cartCount.textContent = totalItems;
        }

        // Handle empty cart
        if (cart.length === 0) {
            if (cartContent) {
                cartContent.innerHTML = '<p class="empty-cart">Your cart is empty</p>';
            }
            if (subtotalElement) subtotalElement.textContent = '0 TK';
            if (grandTotalElement) grandTotalElement.textContent = '60 TK';
            if (checkoutBtn) checkoutBtn.disabled = true;
            return;
        }

        // Calculate totals
        const subtotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        const deliveryFee = 60;
        const grandTotal = subtotal + deliveryFee;

        if (subtotalElement) subtotalElement.textContent = subtotal.toFixed(2) + ' TK';
        if (grandTotalElement) grandTotalElement.textContent = grandTotal.toFixed(2) + ' TK';
        if (checkoutBtn) checkoutBtn.disabled = false;

        // Update cart content
        if (cartContent) {
            cartContent.innerHTML = cart.map(item => `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${escapeHtml(item.name)}</div>
                        <div class="cart-item-price">${item.price.toFixed(2)} TK each</div>
                    </div>
                    <div class="cart-item-quantity">
                        <button class="quantity-btn minus" data-id="${escapeHtml(item.id)}">-</button>
                        <span class="item-quantity">${item.quantity}</span>
                        <button class="quantity-btn plus" data-id="${escapeHtml(item.id)}">+</button>
                        <button class="remove-item" data-id="${escapeHtml(item.id)}">Remove</button>
                    </div>
                    <div class="cart-item-total">${(item.price * item.quantity).toFixed(2)} TK</div>
                </div>
            `).join('');

            // Add event listeners for quantity buttons
            cartContent.querySelectorAll('.quantity-btn.plus').forEach(btn => {
                btn.addEventListener('click', function() {
                    const itemId = this.dataset.id;
                    const item = cart.find(item => item.id === itemId);
                    if (item) {
                        item.quantity += 1;
                        localStorage.setItem('cart', JSON.stringify(cart));
                        updateCartDisplay();
                    }
                });
            });

            cartContent.querySelectorAll('.quantity-btn.minus').forEach(btn => {
                btn.addEventListener('click', function() {
                    const itemId = this.dataset.id;
                    const item = cart.find(item => item.id === itemId);
                    if (item) {
                        item.quantity -= 1;
                        if (item.quantity === 0) {
                            cart = cart.filter(i => i.id !== itemId);
                        }
                        localStorage.setItem('cart', JSON.stringify(cart));
                        updateCartDisplay();
                    }
                });
            });

            cartContent.querySelectorAll('.remove-item').forEach(btn => {
                btn.addEventListener('click', function() {
                    const itemId = this.dataset.id;
                    cart = cart.filter(item => item.id !== itemId);
                    localStorage.setItem('cart', JSON.stringify(cart));
                    updateCartDisplay();
                    showNotification('Item removed from cart', 'success');
                });
            });
        }
    }

    // Checkout functionality
    const checkoutBtn = document.getElementById('checkoutBtn');
    const checkoutModal = document.getElementById('checkoutModal');
    const orderConfirmation = document.getElementById('orderConfirmation');

    if (checkoutBtn && checkoutModal) {
        checkoutBtn.addEventListener('click', function() {
            if (cart.length === 0) return;
            
            if (cartOverlay) cartOverlay.classList.remove('active');
            checkoutModal.classList.add('active');
            document.body.classList.add('modal-open');
        });
    }

    // Close modal handlers
    document.querySelectorAll('.close-modal, .close-checkout').forEach(btn => {
        btn.addEventListener('click', function() {
            if (checkoutModal) checkoutModal.classList.remove('active');
            if (orderConfirmation) orderConfirmation.classList.remove('active');
            document.body.classList.remove('modal-open');
        });
    });

    // Order form submission
    const orderForm = document.getElementById('orderForm');
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (cart.length === 0) {
                showNotification('Your cart is empty', 'error');
                return;
            }

            const formData = new FormData(this);
            const orderData = {
                name: formData.get('name'),
                phone: formData.get('phone'),
                address: formData.get('address'),
                instructions: formData.get('instructions'),
                cart: cart,
                total: parseFloat(document.getElementById('grandTotal').textContent) || 0
            };

            // Basic validation
            if (!orderData.name || !orderData.phone || !orderData.address) {
                showNotification('Please fill in all required fields', 'error');
                return;
            }

            // Submit order
            fetch('place_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    name: orderData.name,
                    phone: orderData.phone,
                    address: orderData.address,
                    instructions: orderData.instructions,
                    cart: JSON.stringify(orderData.cart),
                    total: orderData.total
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (checkoutModal) checkoutModal.classList.remove('active');
                    if (orderConfirmation) {
                        orderConfirmation.classList.add('active');
                        document.getElementById('orderNumber').textContent = data.order_number;
                    }
                    
                    // Clear cart
                    cart = [];
                    localStorage.setItem('cart', JSON.stringify(cart));
                    updateCartDisplay();
                    
                    showNotification('Order placed successfully!', 'success');
                } else {
                    showNotification(data.message || 'Order failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Order failed. Please try again.', 'error');
            });
        });
    }

    // New order button
    const newOrderBtn = document.getElementById('newOrder');
    if (newOrderBtn) {
        newOrderBtn.addEventListener('click', function() {
            if (orderConfirmation) orderConfirmation.classList.remove('active');
            document.body.classList.remove('modal-open');
        });
    }

    // Notification system
    function showNotification(message, type = 'success') {
        // Remove existing notifications
        document.querySelectorAll('.notification').forEach(notif => notif.remove());

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('active');
        }, 100);

        setTimeout(() => {
            notification.classList.remove('active');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 400);
        }, 3000);
    }

// Enhanced image error handling
function handleHeroImageError(img) {
    console.log('Hero image failed to load:', img.src);
    img.style.display = 'none';
    
    const fallbackHTML = `
        <div class="hero-fallback">
            <h3>KACCHI KHACCHI</h3>
            <p>Authentic Biryani</p>
            <small>Hero image loading...</small>
        </div>
    `;
    img.parentElement.innerHTML = fallbackHTML;
}

// Enhanced image loading
function enhanceImages() {
    document.querySelectorAll('img').forEach(img => {
        // Add loading lazy for better performance (except hero image)
        if (!img.hasAttribute('loading')) {
            if (img.parentElement.classList.contains('hero-image')) {
                img.setAttribute('loading', 'eager');
            } else {
                img.setAttribute('loading', 'lazy');
            }
        }
        
        img.addEventListener('load', function() {
            this.classList.add('loaded');
            
            // Special handling for hero image
            if (this.parentElement.classList.contains('hero-image')) {
                this.style.opacity = '1';
                this.style.transform = 'translateY(0)';
            }
        });
        
        img.addEventListener('error', function() {
            console.log('Image failed to load:', this.src);
            this.style.display = 'none';
            
            // Show placeholder if available
            const placeholder = this.nextElementSibling;
            if (placeholder && placeholder.classList.contains('image-placeholder')) {
                placeholder.style.display = 'flex';
            }
            
            // Handle hero image specifically
            if (this.parentElement.classList.contains('hero-image')) {
                handleHeroImageError(this);
            }
        });
        
        // Pre-load hero image with better handling
        if (img.parentElement.classList.contains('hero-image')) {
            img.style.opacity = '0';
            img.style.transform = 'translateY(20px)';
            img.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
        }
    });
}

    // Utility function to escape HTML
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Close cart when clicking outside
    if (cartOverlay) {
        cartOverlay.addEventListener('click', function(e) {
            if (e.target === cartOverlay) {
                cartOverlay.classList.remove('active');
                document.body.classList.remove('modal-open');
            }
        });
    }

    // Close modals when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
                document.body.classList.remove('modal-open');
            }
        });
    });

    // Initialize image enhancement
    enhanceImages();

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});