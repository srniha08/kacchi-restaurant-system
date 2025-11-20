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

// Check if item exists
$stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: admin_dashboard.php');
    exit;
}

// Delete the item
$stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
if ($stmt->execute([$id])) {
    $_SESSION['success'] = "Menu item '{$item['name']}' deleted successfully!";
} else {
    $_SESSION['error'] = "Failed to delete menu item.";
}

header('Location: admin_dashboard.php');
exit;
?>