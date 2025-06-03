<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$host = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "blood-donation";

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

function checkQuery($query, $conn) {
    $result = $conn->query($query);
    if (!$result) {
        die(json_encode(['error' => 'SQL Error: ' . $conn->error . ' - Query: ' . $query]));
    }
    return $result;
}

$total_donors = checkQuery("SELECT COUNT(*) as total FROM donors", $conn)->fetch_assoc()['total'];
$total_donations = checkQuery("SELECT COUNT(*) as total FROM donations", $conn)->fetch_assoc()['total'];
$pending_requests = checkQuery("SELECT COUNT(*) as total FROM requests WHERE status = 'pending'", $conn)->fetch_assoc()['total'];
$blood_units = checkQuery("SELECT SUM(quantity) as total FROM blood_units", $conn)->fetch_assoc()['total'];

$blood_availability = [];
$blood_groups = ['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-', 'AB-'];
foreach ($blood_groups as $group) {
    $result = checkQuery("SELECT SUM(quantity) as total FROM blood_units WHERE blood_group = '$group'", $conn)->fetch_assoc();
    $blood_availability[] = $result['total'] ?? 0;
}

$monthly_donations = [];
$monthly_requests = [];
for ($i = 1; $i <= 7; $i++) {
    $result = checkQuery("SELECT COUNT(*) as total FROM donations WHERE MONTH(date) = $i", $conn)->fetch_assoc();
    $monthly_donations[] = $result['total'] ?? 0;

    $result = checkQuery("SELECT COUNT(*) as total FROM requests WHERE MONTH(date) = $i", $conn)->fetch_assoc();
    $monthly_requests[] = $result['total'] ?? 0;
}

echo json_encode([
    'total_donors' => $total_donors,
    'total_donations' => $total_donations,
    'pending_requests' => $pending_requests,
    'blood_units' => $blood_units,
    'blood_availability' => $blood_availability,
    'monthly_donations' => $monthly_donations,
    'monthly_requests' => $monthly_requests
]);

$conn->close();
?>
