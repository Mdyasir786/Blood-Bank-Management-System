<?php
$host = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "blood-donation";

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

$result = $conn->query("SELECT SUM(quantity) AS total_stock FROM blood_stock");
$row = $result->fetch_assoc();
echo json_encode(["total_stock" => $row['total_stock'] ?? 0]);

$conn->close();
?>
