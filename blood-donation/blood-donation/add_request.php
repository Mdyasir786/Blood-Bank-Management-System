<?php
header('Content-Type: application/json');
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
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}
$errors = [];
$requiredFields = [
    'patient-name' => 'Patient Name',
    'gender' => 'Gender',
    'blood-group' => 'Blood Group',
    'city' => 'City',
    'contact' => 'Contact',
    'hospital' => 'Hospital',
    'urgency' => 'Urgency'
];
foreach ($requiredFields as $field => $name) {
    if (empty($_POST[$field])) {
        $errors[] = "$name is required";
    }
}
if (!empty($errors)) {
    echo json_encode(['error' => implode(', ', $errors)]);
    exit();
}
$patient_name = $conn->real_escape_string(trim($_POST['patient-name']));
$gender = $conn->real_escape_string(trim($_POST['gender']));
$blood_group = $conn->real_escape_string(trim($_POST['blood-group']));
$city = $conn->real_escape_string(trim($_POST['city']));
$contact = $conn->real_escape_string(trim($_POST['contact']));
$hospital = $conn->real_escape_string(trim($_POST['hospital']));
$urgency = $conn->real_escape_string(trim($_POST['urgency']));
$validBloodGroups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
if (!in_array($blood_group, $validBloodGroups)) {
    echo json_encode(['error' => 'Invalid blood group']);
    exit();
}
$validUrgency = ['Low', 'Medium', 'High'];
if (!in_array($urgency, $validUrgency)) {
    echo json_encode(['error' => 'Invalid urgency level']);
    exit();
}
$stmt = $conn->prepare("INSERT INTO requests (patient_name, gender, blood_group, city, contact, hospital, urgency, status, date, requested_by) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', CURDATE(), ?)");
$requested_by = $_SESSION['user_id'];
$stmt->bind_param("sssssssi", $patient_name, $gender, $blood_group, $city, $contact, $hospital, $urgency, $requested_by);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Request added successfully']);
} else {
    echo json_encode(['error' => $stmt->error]);
}
$stmt->close();
$conn->close();
?>