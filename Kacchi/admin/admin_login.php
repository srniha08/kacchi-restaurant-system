<?php
session_start();
require_once '../db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $errors = [];

    if (empty($username) || empty($password)) {
        $errors[] = "Both username and password are required";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_id'] = $admin['id'];
            header('Location: admin_dashboard.php');
            exit;
        } else {
            $errors[] = "Invalid username or password";
        }
    }
    
    // If we get here, there was an error
    $error = implode("<br>", $errors);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - KACCHI KHACCHI</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@400;500;600&family=Poppins:wght@300;400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container">
        <form class="auth-form" method="POST" action="">
            <h2>Admin Login</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Your admin username...🦸"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required 
                       placeholder="The secret recipe to success... 🔐">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Access Kitchen Control 🚀</button>
            
            <div style="text-align: center; margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; color: #7C7C7C; font-style: italic;">
                <h4 style="color: #7A4E2A; margin-bottom: 10px;">💼 Admin Access Only</h4>
                <p style="margin: 5px 0; font-size: 0.9rem;">This area is for restaurant management only</p>
            </div>
            
            <p style="text-align: center; margin-top: 10px;">
                <a href="../index.php" style="color: #7A4E2A; text-decoration: none;">
                    ← Back to Main Website (where the real food is! 🎉)
                </a>
            </p>
        </form>
    </div>
</body>
</html>