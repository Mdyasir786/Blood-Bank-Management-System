<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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

$sql = "SELECT * FROM handed_over";
$result = $conn->query($sql);
$handedOver = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $handedOver[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handed Over - Blood Bank Management</title>
    <link rel="stylesheet" href="common.css">
    <link rel="stylesheet" href="handed-over.css">
    <link rel="stylesheet" href="chatbot.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <header>
        <h1>Blood Bank Management System - Handed Over Records</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <button id="logout-btn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>
    </header>
    
    <div class="sidebar">
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="donors.php"><i class="fas fa-user-friends"></i> Donors</a>
        <a href="blood-donation.php"><i class="fas fa-tint"></i> Blood Donation</a>
        <a href="requests.php"><i class="fas fa-hand-holding-heart"></i> Requests</a>
        <a href="handed-over.php" class="active"><i class="fas fa-check-circle"></i> Handed Over</a>
        <?php if ($_SESSION['role'] === 'Admin'): ?>
            <a href="users.php"><i class="fas fa-users-cog"></i> Users</a>
            <a href="admin_dashboard.php"><i class="fas fa-user-shield"></i> Admin Dashboard</a>
        <?php endif; ?>
    </div>
    
    <div class="container">
        <h2><i class="fas fa-exchange-alt"></i> Handed-Over Records</h2>        
        <div class="filter-search">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="search-bar" placeholder="Search by Request ID, Recipient, or Hospital..." />
            </div>
            <select id="filter">
                <option value="">Filter by Status</option>
                <option value="Delivered">Delivered</option>
                <option value="In Transit">In Transit</option>
                <option value="Pending">Pending</option>
            </select>
        </div>
        
        <div class="table-container">
            <div class="table-scroll">
                <table id="handed-over-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-id-card"></i> Handover ID</th>
                            <th><i class="fas fa-file-alt"></i> Request ID</th>
                            <th><i class="fas fa-flask"></i> Quantity (ml)</th>
                            <th><i class="fas fa-calendar-day"></i> Handover Date</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                        </tr>
                    </thead>
                    <tbody id="handed-over-table-body">
                        <?php foreach ($handedOver as $record): ?>
                            <tr>
                                <td><?php echo $record['handover_id']; ?></td>
                                <td><?php echo $record['request_id']; ?></td>
                                <td><?php echo $record['quantity']; ?></td>
                                <td><?php echo $record['handover_date']; ?></td>
                                <td><span class="status <?php echo strtolower(str_replace(' ', '-', $record['status'])); ?>"><?php echo $record['status']; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include 'includes/chatbot.php'; ?>
    <script>
        document.getElementById('logout-btn').addEventListener('click', function() {
            window.location.href = 'logout.php';
        });

        function fetchHandedOver() {
            $.ajax({
                url: 'fetch_handed_over.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    let tableBody = '';
                    response.forEach(record => {
                        tableBody += `
                            <tr>
                                <td>${record.handover_id}</td>
                                <td>${record.request_id}</td>
                                <td>${record.quantity}</td>
                                <td>${record.handover_date}</td>
                                <td><span class="status ${record.status.toLowerCase().replace(' ', '-')}">${record.status}</span></td>
                            </tr>
                        `;
                    });
                    $('#handed-over-table-body').html(tableBody);
                }
            });
        }

        $('#search-bar').on('input', function() {
            let searchVal = $(this).val().toLowerCase();
            $('#handed-over-table-body tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(searchVal) > -1);
            });
        });

        $('#filter').on('change', function() {
            let filterVal = $(this).val().toLowerCase();
            $('#handed-over-table-body tr').filter(function() {
                $(this).toggle($(this).find('.status').text().toLowerCase() === filterVal || filterVal === '');
            });
        });

        fetchHandedOver();
    </script>
</body>
</html>