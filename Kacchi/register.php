<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email already registered";
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $phone, $address, $hashed_password])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_phone'] = $phone;
                $_SESSION['user_address'] = $address;
                
                header('Location: index.php');
                exit;
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - KACCHI KHACCHI</title>
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
                            <a href="login.php" class="login-btn">Login</a>
                        </div>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <form class="auth-form" method="POST" action="">
            <h2>Join the Kacchi Family! 🎉</h2>
            <p style="text-align: center; color: #7A4E2A; margin-bottom: 25px; font-style: italic;">
                Fill this form and let the Kacchi adventures begin! 🥘
            </p>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" required 
                       placeholder="What should we call you? 👑"
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required
                       placeholder="your.kacchi.craving@email.com 🍛"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" required
                       placeholder="Where should we call with good news? 📞"
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="address">Delivery Address *</label>
                <textarea id="address" name="address" required 
                          placeholder="Where should we deliver your happiness? 🏠
Example: House 42, Kacchi Lane, Biryani Road, Narayanganj"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required
                       placeholder="Create a secret code (6+ characters) 🔐">
                <small style="color: #9A6B45; font-style: italic;">Make it strong like our Kacchi flavors! 💪</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       placeholder="Repeat your secret code, just to be sure! 🔁">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Join & Start Ordering! 🚀
            </button>
            
            <p style="text-align: center; margin-top: 20px;">
                Already part of the family? <a href="login.php">Login here! 😊</a>
            </p>

            <div style="text-align: center; margin-top: 25px; padding: 15px; background: rgba(214, 168, 90, 0.1); border-radius: 8px;">
                <p style="margin: 0; color: #7A4E2A; font-size: 0.9rem;">
                    <strong>🎁 Welcome Bonus:</strong> Your first order comes with extra love! 💝
                </p>
            </div>
        </form>
    </div>

    <style>
    .auth-form {
        max-width: 450px;
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

    .form-group input,
    .form-group textarea {
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(122, 78, 42, 0.2);
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: #9A6B45;
        opacity: 0.7;
        font-size: 0.9rem;
    }

    .form-group textarea {
        min-height: 80px;
        resize: vertical;
    }

    small {
        display: block;
        margin-top: 5px;
        font-size: 0.8rem;
    }
    </style>
</body>
</html>