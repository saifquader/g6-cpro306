<?php
// logout.php
require_once 'config/database.php';

// Remove remember me token from DB and unset cookie
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $tokenHash = hash('sha256', $token);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE token_hash = ?");
        $stmt->execute([$tokenHash]);
    } catch(PDOException $e) {}
    
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy session
session_unset();
session_destroy();

redirect('index.php');
?>
