<?php
session_start();
if(isset($_SESSION['user'])) {
    header("Location: welcome.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <form action="register_user.php" method="post">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="index.php">Login here</a></p>
</body>
</html>
