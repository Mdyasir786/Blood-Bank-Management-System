<?php
$host = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "blood-donation";

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$bloodGroupFilter = isset($_GET['blood_group']) ? $conn->real_escape_string($_GET['blood_group']) : "";
$dateRangeFilter = isset($_GET['date_range']) ? $_GET['date_range'] : "";

$query = "SELECT donor_name, blood_group, status, DATE_FORMAT(date, '%Y-%m-%d') AS date FROM donations WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (donor_name LIKE '%$search%' OR blood_group LIKE '%$search%')";
}

if (!empty($bloodGroupFilter)) {
    $query .= " AND blood_group = '$bloodGroupFilter'";
}

if ($dateRangeFilter == "last-week") {
    $query .= " AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($dateRangeFilter == "last-month") {
    $query .= " AND date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
} elseif ($dateRangeFilter == "last-year") {
    $query .= " AND date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
}

$query .= " ORDER BY date DESC";

$result = $conn->query($query);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
?>
