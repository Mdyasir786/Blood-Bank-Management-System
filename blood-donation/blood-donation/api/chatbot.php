<?php
session_start();
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "blood-donation");
if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}
$input = json_decode(file_get_contents('php://input'), true);
$question = trim(strtolower($input['question'] ?? ''));
if (empty($question)) {
    die(json_encode(['response' => "Please type your question"]));
}
$user_id = $_SESSION['user_id'] ?? null;
$conn->query("INSERT INTO chatbot_questions (question, user_id) VALUES ('$question', $user_id)");
if (preg_match('/hi|hello|hey/', $question)) {
    die(json_encode(['response' => "Hello! How can I help you with blood donation today?"]));
}
if (preg_match('/how are you|how you doing/', $question)) {
    die(json_encode(['response' => "I'm doing great! How about you?"]));
}
$result = $conn->query("SELECT response FROM chatbot_responses 
    WHERE '$question' REGEXP question_pattern LIMIT 1");

if ($result->num_rows > 0) {
    $response = $result->fetch_assoc()['response'];
} 
elseif (preg_match('/(A\+|A-|B\+|B-|O\+|O-|AB\+|AB-)/i', $question, $matches)) {
    $blood_type = strtoupper($matches[0]);
    $stock = $conn->query("SELECT SUM(quantity) FROM blood_stock WHERE blood_group='$blood_type'")->fetch_row()[0];
    $response = "Current $blood_type stock: $stock units";
}
elseif (preg_match('/book|schedule|appointment/', $question)) {
    $response = "To book, please provide:\n1. Date (YYYY-MM-DD)\n2. Blood type\n3. Purpose\n\nExample: 'Book for 2023-12-25, O+, donation'";
} 
else {
    $response = "I'll forward this to our team. For quick help, ask about:\n- Eligibility\n- Blood request\n- Donation process";
}
if (preg_match('/book (?:appointment )?for (\d{4}-\d{2}-\d{2}), ([A-Za-z\+-]+), (.+)/i', $question, $matches)) {
    $date = $matches[1];
    $blood_type = strtoupper($matches[2]);
    $purpose = $matches[3];
    
    $stmt = $conn->prepare("INSERT INTO appointments (user_id, appointment_date, blood_type, purpose) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $date, $blood_type, $purpose);
    
    if ($stmt->execute()) {
        $response = "✅ Booked for $date ($blood_type). We'll email you confirmation.";
    } else {
        $response = "❌ Booking failed. Please call 1800-123-4567";
    }
}

echo json_encode(['response' => $response]);
$conn->close();
?>