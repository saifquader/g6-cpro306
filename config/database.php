<?php
// config/database.php
session_start();

$host = 'localhost';
$db   = 'ndis_db';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If the database doesn't exist, we can handle it or show a friendly message.
    // For development, we'll just show the error.
    die("Database Connection failed: " . $e->getMessage());
}

// Helper function to generate UUIDs
function generate_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

// Redirect helper
function redirect($url) {
    header("Location: " . $url);
    exit;
}

// Authentication Check
function check_auth() {
    if (!isset($_SESSION['user_id'])) {
        redirect('index.php');
    }
}
?>
