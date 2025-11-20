<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);
    $image_url = trim($_POST['image_url']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    $errors = [];

    if (empty($name)) $errors[] = "Item name is required";
    if (empty($description)) $errors[] = "Description is required";
    if ($price <= 0) $errors[] = "Valid price is required";
    if (empty($category)) $errors[] = "Category is required";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO menu_items (name, description, price, category, image_url, is_available) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $description, $price, $category, $image_url, $is_available])) {
            $_SESSION['success'] = "Menu item added successfully!";
            header('Location: admin_dashboard.php'); // FIXED REDIRECT
            exit;
        } else {
            $error = "Failed to add menu item. Please try again.";
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

// Check for success message from redirect
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Menu Item - KACCHI KHACCHI</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@400;500;600&family=Poppins:wght@300;400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">
    <header class="admin-header">
        <div class="container">
            <nav class="admin-nav">
                <div class="logo">
                    <h1>Add Menu Item</h1>
                </div>
                <ul>
                    <li><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="add_item.php" class="active">Add Item</a></li>
                    <li><a href="admin_logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="admin-main">
        <div class="container">
            <form class="admin-form" method="POST" action="">
                <h2>Add New Menu Item</h2>
                
                <?php if (isset($success)): ?>
                    <div class="success-message">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Item Name *</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="Enter delicious item name"
                           value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" required placeholder="Describe this amazing dish..."><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (TK) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required 
                               placeholder="0.00"
                               value="<?php echo isset($price) ? $price : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Signature Kacchi" <?php echo (isset($category) && $category == 'Signature Kacchi') ? 'selected' : ''; ?>>Signature Kacchi</option>
                            <option value="Weekly Special" <?php echo (isset($category) && $category == 'Weekly Special') ? 'selected' : ''; ?>>Weekly Special</option>
                            <option value="Sides & Drinks" <?php echo (isset($category) && $category == 'Sides & Drinks') ? 'selected' : ''; ?>>Sides & Drinks</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image_url">Picture URL</label>
                    <input type="url" id="image_url" name="image_url" 
                           placeholder="Paste image URL (optional)"
                           value="<?php echo isset($image_url) ? htmlspecialchars($image_url) : ''; ?>">
                    
                    <?php if (isset($image_url) && !empty($image_url)): ?>
                        <div class="image-preview">
                            <img src="<?php echo htmlspecialchars($image_url); ?>" alt="Image Preview" onerror="this.style.display='none'">
                            <p>Image Preview</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="checkbox-group">
                        <input type="checkbox" id="is_available" name="is_available" <?php echo (!isset($is_available) || $is_available) ? 'checked' : ''; ?>>
                        <span>Available for ordering</span>
                    </label>
                </div>

                <div class="form-actions">
                    <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Menu Item</button>
                </div>
            </form>
        </div>
    </main>

    <script>
    // Live image preview
    document.getElementById('image_url').addEventListener('input', function() {
        const preview = this.parentElement.querySelector('.image-preview');
        const img = preview?.querySelector('img');
        
        if (this.value && img) {
            img.src = this.value;
            preview.style.display = 'block';
        }
    });

    // Create image preview container if it doesn't exist
    const imageUrlInput = document.getElementById('image_url');
    if (imageUrlInput && !imageUrlInput.parentElement.querySelector('.image-preview')) {
        const previewDiv = document.createElement('div');
        previewDiv.className = 'image-preview';
        previewDiv.style.display = 'none';
        previewDiv.innerHTML = `
            <img src="" alt="Image Preview" onerror="this.style.display='none'">
            <p>Image Preview</p>
        `;
        imageUrlInput.parentElement.appendChild(previewDiv);
    }
    </script>
</body>
</html>