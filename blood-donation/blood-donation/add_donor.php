<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); 
    echo json_encode(['error' => 'Unauthorized: Please log in first.']);
    exit();
}
$host = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "blood-donation";
$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    http_response_code(500); 
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$blood_group = isset($_POST['blood-group']) ? trim($_POST['blood-group']) : '';
$gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
$last_donation = isset($_POST['last-donation']) ? trim($_POST['last-donation']) : null;
if (empty($name) || empty($blood_group) || empty($gender) || empty($city) || empty($contact)) {
    http_response_code(400); 
    echo json_encode(['error' => 'All fields except "Last Donation Date" are required.']);
    exit();
}
$stmt = $conn->prepare("INSERT INTO donors (name, blood_group, gender, city, contact, last_donation, status) VALUES (?, ?, ?, ?, ?, ?, 'Available')");
$stmt->bind_param("ssssss", $name, $blood_group, $gender, $city, $contact, $last_donation);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500); 
    echo json_encode(['error' => 'Failed to add donor: ' . $stmt->error]);
}
$stmt->close();
$conn->close();
?>