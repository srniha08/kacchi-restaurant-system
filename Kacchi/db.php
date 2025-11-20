<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'kacchi_restaurant';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Check if tables exist
    $tablesExist = false;
    try {
        $result = $pdo->query("SELECT 1 FROM admin_users LIMIT 1");
        $tablesExist = true;
    } catch (Exception $e) {
        $tablesExist = false;
    }
    
    // Only run setup if tables don't exist
    if (!$tablesExist) {
        $sqlFile = __DIR__ . '/../Database/kacchi_restaurant.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            
            // Split SQL statements and execute them one by one
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // Ignore duplicate key errors during setup
                        if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                            throw $e;
                        }
                    }
                }
            }
        }
    }
    
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>