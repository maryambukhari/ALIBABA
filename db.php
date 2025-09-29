<?php
// Database connection file - Professional setup with error handling and PDO for security (better than mysqli for prepared statements).
$host = "localhost"; // Assuming localhost; change if needed.
$dbname = "dbpnjk2csij1j5";
$username = "uasxxqbztmxwm";
$password = "wss863wqyhal";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
