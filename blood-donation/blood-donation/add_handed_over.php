<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
if ($_SESSION['role'] !== 'Admin') {
    echo json_encode(['error' => 'Access Denied']);
    exit();
}
$host = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "blood-donation";
$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$request_id = $_POST['request-id'];
$blood_group = $_POST['blood-group'];
$quantity = $_POST['quantity'];
$handover_date = $_POST['handover-date'];
$status = $_POST['status'];
$stmt = $conn->prepare("INSERT INTO handed_over (request_id, blood_group, quantity, handover_date, status) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssiss", $request_id, $blood_group, $quantity, $handover_date, $status);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $stmt->error]);
}
$stmt->close();
$conn->close();
?>