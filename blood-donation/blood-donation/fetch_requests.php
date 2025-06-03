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
    die("Connection failed: " . $conn->connect_error);
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$bloodGroup = isset($_GET['blood_group']) ? $conn->real_escape_string($_GET['blood_group']) : '';
$status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$urgency = isset($_GET['urgency']) ? $conn->real_escape_string($_GET['urgency']) : '';

$sql = "SELECT * FROM requests WHERE 1";

if (!empty($search)) {
    $sql .= " AND (LOWER(patient_name) LIKE LOWER('%$search%') OR LOWER(blood_group) LIKE LOWER('%$search%'))";
}
if (!empty($bloodGroup)) {
    $sql .= " AND blood_group = '$bloodGroup'";
}
if (!empty($status)) {
    $sql .= " AND status = '$status'";
}
if (!empty($urgency)) {
    $sql .= " AND urgency = '$urgency'";
}

$result = $conn->query($sql);
$requests = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}

$conn->close();
echo json_encode($requests);
?>
