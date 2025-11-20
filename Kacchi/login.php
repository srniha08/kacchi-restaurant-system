<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $errors = [];

    if (empty($email) || empty($password)) {
        $errors[] = "Both email and password are required";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'];
            $_SESSION['user_address'] = $user['address'];
            
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Invalid email or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KACCHI KHACCHI</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@400;500;600&family=Poppins:wght@300;400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
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
                        <div class="user-auth">
                            <a href="register.php" class="register-btn">Register</a>
                        </div>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <form class="auth-form" method="POST" action="">
            <h2>Welcome Back! 😋</h2>
            <p style="text-align: center; color: #7A4E2A; margin-bottom: 25px; font-style: italic;">
                Ready to order some delicious Kacchi? Your taste buds are waiting!
            </p>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required 
                       placeholder="your.hunger@kacchi.com 🍛"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required
                       placeholder="Shh... it's a secret recipe! 🤫">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Let Me In - I'm Hungry! 🎉
            </button>
            
            <p style="text-align: center; margin-top: 20px;">
                Don't have an account? <a href="register.php">Join the Kacchi family! 🥘</a>
            </p>

            <div style="text-align: center; margin-top: 25px; padding: 15px; background: rgba(214, 168, 90, 0.1); border-radius: 8px;">
                <p style="margin: 0; color: #7A4E2A; font-size: 0.9rem;">
                    <strong>Pro Tip:</strong> Faster login = Faster Kacchi! 🚀
                </p>
            </div>
        </form>
    </div>

    <style>
    .auth-form {
        max-width: 400px;
        margin: 50px auto;
        padding: 35px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(122, 78, 42, 0.1);
    }
    
    .error-message {
        background: rgba(201, 127, 94, 0.1);
        color: #C97F5E;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 20px;
        border-left: 4px solid #C97F5E;
    }
    
    .error-message p {
        margin: 0;
    }

    .form-group input {
        transition: all 0.3s ease;
    }

    .form-group input:focus {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(122, 78, 42, 0.2);
    }

    .form-group input::placeholder {
        color: #9A6B45;
        opacity: 0.7;
    }
    </style>
</body>
</html>