<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$host = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "blood-donation";

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

$sql = "SELECT * FROM handed_over";
$result = $conn->query($sql);
$handedOver = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $handedOver[] = $row;
    }
}

echo json_encode($handedOver);
$conn->close();
?>
