<?php
require_once 'db.php';

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);

$query = "INSERT INTO users (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $username, $password);

if ($stmt->execute()) {
    echo "Registration successful. <a href='index.php'>Login here</a>";
} else {
    echo "Error: " . $stmt->error;
}
?>
