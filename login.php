<?php
session_start();
require_once 'db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$query = "SELECT * FROM br_users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && $password === $user['password']) {
    $_SESSION['user'] = $user['username'];
    header("Location: welcome.php");
} else {
    echo "Invalid login credentials.";
}
?>
