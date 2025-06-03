<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbName = "blood-donation";

    $conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $blood_group = $_POST['blood_group'];
    $status = "Active"; 
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($password) || strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if (empty($role)) $errors[] = "Role is required";
    if (empty($blood_group)) $errors[] = "Blood group is required";

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: add_user.php");
        exit();
    }
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['errors'] = ["Email already exists"];
        header("Location: add_user.php");
        exit();
    }
    $stmt->close();
    $profile_pic = "default.jpg"; 
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ["jpg", "jpeg", "png", "gif"];

        if (in_array($file_ext, $allowed_ext)) {
            $profile_pic = uniqid("user_", true) . "." . $file_ext;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_dir . $profile_pic);
        } else {
            $_SESSION['errors'] = ["Invalid file type. Only JPG, PNG, GIF allowed"];
            header("Location: add_user.php");
            exit();
        }
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, blood_group, profile_pic, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $email, $hashed_password, $role, $blood_group, $profile_pic, $status);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User added successfully";
        header("Location: users.php");
    } else {
        $_SESSION['errors'] = ["Error adding user: " . $stmt->error];
        header("Location: add_user.php");
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    header("Location: add_user.php");
    exit();
}
?>