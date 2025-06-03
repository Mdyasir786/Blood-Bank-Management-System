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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $handoverId = $_POST['handover_id'];
    $newStatus = $_POST['status'];

    $stmt = $conn->prepare("UPDATE handed_over SET status=? WHERE handover_id=?");
    $stmt->bind_param("si", $newStatus, $handoverId);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Failed to update status"]);
    }

    $stmt->close();
    $conn->close();
}
?>
