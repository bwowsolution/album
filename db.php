<?php
$servername = "31.11.39.90:3306";
$username = "Sql1625188";
$password = "WLcVAPJgpKRa7cM!";
$dbname = "Sql1625188_3";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
