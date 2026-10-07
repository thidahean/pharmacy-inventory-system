<?php
$host = 'db';
$user = 'pharmacy_user';
$pass = 'pharmacy_pass';
$db   = 'pharmacy';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>✅ Connected successfully to MySQL!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>MySQL Server: " . $conn->server_info . "</p>";
echo "<p>Database: " . $db . "</p>";

$conn->close();
?>
