<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link rel="stylesheet" href="common.css">
    <link rel="stylesheet" href="add_user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <h1>Blood Bank Management System</h1>
        <button class="back-btn" onclick="window.location.href='admin_dashboard.php'">
            <i class="fas fa-arrow-left"></i> Back to Users
        </button>
    </header>
    <div class="container">
        <h2>Add New User</h2>
        <form action="process_add_user.php" method="POST" enctype="multipart/form-data" class="user-form">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required placeholder="Enter full name">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required placeholder="Enter email address">
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <div class="password-input">
                    <input type="password" id="password" name="password" required placeholder="Create password">
                    <i class="fas fa-eye toggle-password"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="" disabled selected>Select user role</option>
                    <option value="Admin">Admin</option>
                    <option value="Donor">Donor</option>
                    <option value="Recipient">Recipient</option>
                </select>
            </div>

            <div class="form-group">
                <label for="blood_group">Blood Group:</label>
                <select id="blood_group" name="blood_group" required>
                    <option value="" disabled selected>Select blood group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                </select>
            </div>

            <div class="form-group">
                <label for="profile_pic">Profile Picture (Optional):</label>
                <div class="file-upload">
                    <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                    <label for="profile_pic" class="file-upload-label">
                        <i class="fas fa-cloud-upload-alt"></i> Choose File
                    </label>
                    <span class="file-name">No file chosen</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-user-plus"></i> Add User
                </button>
            </div>
        </form>
    </div>

    <script>
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        document.getElementById('profile_pic').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
            document.querySelector('.file-name').textContent = fileName;
        });

        document.querySelector('.user-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            if (password.length < 8) {
                alert('Password must be at least 8 characters long');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>