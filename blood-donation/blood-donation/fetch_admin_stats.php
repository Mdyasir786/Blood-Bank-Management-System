<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$host = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "blood-donation";

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

$response = [
    'total_donors' => 0,
    'total_donations' => 0,
    'pending_requests' => 0,
    'blood_units' => 0,
    'total_users' => 0,
    'completed_requests' => 0,
    'blood_stock' => array_fill(0, 8, 0)
];

$result = $conn->query("SELECT COUNT(*) as count FROM donors");
if ($result) $response['total_donors'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM donations");
if ($result) $response['total_donations'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status='Pending'");
if ($result) $response['pending_requests'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT SUM(quantity) as total FROM blood_stock");
if ($result) $response['blood_units'] = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) $response['total_users'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM handed_over");
if ($result) $response['completed_requests'] = $result->fetch_assoc()['count'];

$blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
foreach ($blood_groups as $index => $group) {
    $result = $conn->query("SELECT SUM(quantity) as total FROM blood_stock WHERE blood_group='$group'");
    if ($result) {
        $row = $result->fetch_assoc();
        $response['blood_stock'][$index] = $row['total'] ?? 0;
    }
}

$conn->close();
echo json_encode($response);
?>