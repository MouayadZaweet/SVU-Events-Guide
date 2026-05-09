<?php
// =====================================
// Database Connection - PDO
// =====================================

// Database credentials
$host = 'localhost';
$dbname = 'svu_events';
$username = 'root';
$password = '';

try {
    // Create PDO instance
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Fetch as associative array
            PDO::ATTR_EMULATE_PREPARES   => false,                   // Use real prepared statements
        ]
    );
} catch (PDOException $e) {
    // Terminate and display error if connection fails
    die("Database Connection Failed: " . $e->getMessage());
}

// =====================================
// Base URL (Dynamic)
// =====================================

// Detects the root URL automatically — works on localhost or any live server (Hosting)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$base_url = $protocol . '://' . $host . $script_dir . '/';
?>