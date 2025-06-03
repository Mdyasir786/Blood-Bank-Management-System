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

$activities = [];
$result = $conn->query("
    (SELECT 'donation' as type, CONCAT('New donation from ', donor_name) as description, 
    donation_date as timestamp, 0 as action_needed
    FROM donations 
    ORDER BY donation_date DESC 
    LIMIT 5)
    
    UNION
    
    (SELECT 'request' as type, CONCAT('New request from ', patient_name) as description, 
    date as timestamp, 1 as action_needed
    FROM requests 
    WHERE status = 'Pending'
    ORDER BY date DESC 
    LIMIT 5)
    
    ORDER BY timestamp DESC 
    LIMIT 5
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
}

$conn->close();
echo json_encode($activities);
?>