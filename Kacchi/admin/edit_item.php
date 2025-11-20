<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$id = $_GET['id'];

// Get current item data
$stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: admin_dashboard.php');
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
        $stmt = $pdo->prepare("UPDATE menu_items SET name = ?, description = ?, price = ?, category = ?, image_url = ?, is_available = ? WHERE id = ?");
        
        if ($stmt->execute([$name, $description, $price, $category, $image_url, $is_available, $id])) {
            $_SESSION['success'] = "Menu item updated successfully!";
            header('Location: admin_dashboard.php'); // FIXED REDIRECT
            exit;
        } else {
            $error = "Failed to update menu item. Please try again.";
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
    <title>Edit Menu Item - KACCHI KHACCHI</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@400;500;600&family=Poppins:wght@300;400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">
    <header class="admin-header">
        <div class="container">
            <nav class="admin-nav">
                <div class="logo">
                    <h1>Edit Menu Item</h1>
                </div>
                <ul>
                    <li><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="add_item.php">Add Item</a></li>
                    <li><a href="admin_logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="admin-main">
        <div class="container">
            <form class="admin-form" method="POST" action="">
                <h2>Edit Menu Item</h2>
                
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
                           value="<?php echo htmlspecialchars($item['name']); ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($item['description']); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (TK) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required 
                               value="<?php echo $item['price']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="Signature Kacchi" <?php echo $item['category'] == 'Signature Kacchi' ? 'selected' : ''; ?>>Signature Kacchi</option>
                            <option value="Weekly Special" <?php echo $item['category'] == 'Weekly Special' ? 'selected' : ''; ?>>Weekly Special</option>
                            <option value="Sides & Drinks" <?php echo $item['category'] == 'Sides & Drinks' ? 'selected' : ''; ?>>Sides & Drinks</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image_url">Picture URL</label>
                    <input type="url" id="image_url" name="image_url" 
                           placeholder="Paste image URL"
                           value="<?php echo htmlspecialchars($item['image_url']); ?>">
                    
                    <?php if (!empty($item['image_url'])): ?>
                        <div class="image-preview">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Image Preview" onerror="this.style.display='none'">
                            <p>Current Image</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="checkbox-group">
                        <input type="checkbox" id="is_available" name="is_available" <?php echo $item['is_available'] ? 'checked' : ''; ?>>
                        <span>Available for ordering</span>
                    </label>
                </div>

                <div class="form-actions">
                    <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Item</button>
                </div>
            </form>
        </div>
    </main>

    <script>
    // Live image preview
    document.getElementById('image_url').addEventListener('input', function() {
        const preview = this.parentElement.querySelector('.image-preview');
        let img = preview?.querySelector('img');
        
        if (!preview) {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'image-preview';
            previewDiv.innerHTML = `
                <img src="" alt="Image Preview" onerror="this.style.display='none'">
                <p>Image Preview</p>
            `;
            this.parentElement.appendChild(previewDiv);
            img = previewDiv.querySelector('img');
        }
        
        if (this.value && img) {
            img.src = this.value;
            preview.style.display = 'block';
        }
    });
    </script>
</body>
</html>