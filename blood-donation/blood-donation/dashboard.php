<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] == 1) {
    header("Location: login.php");
    exit();
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];
$host = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "blood-donation";

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$bloodStock = [
    'A+' => 0,
    'A-' => 0,
    'B+' => 0,
    'B-' => 0,
    'O+' => 0,
    'O-' => 0,
    'AB+' => 0,
    'AB-' => 0
];

$result = $conn->query("SELECT blood_group, SUM(quantity) as total FROM blood_stock GROUP BY blood_group");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (array_key_exists($row['blood_group'], $bloodStock)) {
            $bloodStock[$row['blood_group']] = (int)$row['total'];
        }
    }
}

$totalStock = array_sum($bloodStock);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Blood Bank Management</title>
    <link rel="stylesheet" href="common.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="chatbot.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <header>
        <h1>Blood Bank Management System - Dashboard</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <button id="logout-btn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>
    </header>
    
    <div class="sidebar">
        <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="donors.php"><i class="fas fa-user-friends"></i> Donors</a>
        <a href="blood-donation.php"><i class="fas fa-tint"></i> Blood Donation</a>
        <a href="requests.php"><i class="fas fa-hand-holding-heart"></i> Requests</a>
        <a href="handed-over.php"><i class="fas fa-check-circle"></i> Handed Over</a>
        <?php if ($_SESSION['role'] === 'Admin'): ?>
            <a href="users.php"><i class="fas fa-users-cog"></i> Users</a>
            <a href="admin_dashboard.php"><i class="fas fa-user-shield"></i> Admin Dashboard</a>
        <?php endif; ?>
    </div>
    
    <div class="container">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h2>
        <p>Welcome to the Blood Bank Management System.</p>
        
        <div class="summary-container">
            <div class="summary-box">
                <i class="fas fa-user-friends icon"></i>
                <p class="counter" id="total-donors" data-target="0">0</p>
                <span>Total Donors</span>
            </div>
            <div class="summary-box">
                <i class="fas fa-tint icon"></i>
                <p class="counter" id="total-donations" data-target="0">0</p>
                <span>Total Blood Donations</span>
            </div>
            <div class="summary-box">
                <i class="fas fa-hand-holding-heart icon"></i>
                <p class="counter" id="pending-requests" data-target="0">0</p>
                <span>Pending Requests</span>
            </div>
            <div class="summary-box">
                <i class="fas fa-warehouse icon"></i>
                <p class="counter" id="blood-units" data-target="<?php echo $totalStock; ?>"><?php echo $totalStock; ?></p>
                <span>Blood Units Available</span>
            </div>
        </div>
        
        <div class="charts-container">
            <div class="chart-box">
                <h3><i class="fas fa-chart-pie"></i> Blood Group Availability</h3>
                <canvas id="bloodAvailabilityChart"></canvas>
            </div>
            <div class="chart-box">
                <h3><i class="fas fa-chart-line"></i> Monthly Donations & Requests</h3>
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
        
        <div class="blood-compatibility-table">
            <h2><i class="fas fa-heartbeat"></i> Blood Type Compatibility</h2>
            <table>
                <thead>
                    <tr>
                        <th>Blood Type</th>
                        <th>Can Donate To</th>
                        <th>Can Receive From</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>A+</td>
                        <td>A+, AB+</td>
                        <td>A+, A-, O+, O-</td>
                    </tr>
                    <tr>
                        <td>B+</td>
                        <td>B+, AB+</td>
                        <td>B+, B-, O+, O-</td>
                    </tr>
                    <tr>
                        <td>AB+</td>
                        <td>AB+</td>
                        <td>All Blood Types</td>
                    </tr>
                    <tr>
                        <td>O+</td>
                        <td>A+, B+, AB+, O+</td>
                        <td>O+, O-</td>
                    </tr>
                    <tr>
                        <td>A-</td>
                        <td>A+, A-, AB+, AB-</td>
                        <td>A-, O-</td>
                    </tr>
                    <tr>
                        <td>B-</td>
                        <td>B+, B-, AB+, AB-</td>
                        <td>B-, O-</td>
                    </tr>
                    <tr>
                        <td>AB-</td>
                        <td>AB+, AB-</td>
                        <td>AB-, A-, B-, O-</td>
                    </tr>
                    <tr>
                        <td>O-</td>
                        <td>All Blood Types</td>
                        <td>O-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php include 'includes/chatbot.php'; ?>
    <script>
        const initialBloodStock = <?php echo json_encode($bloodStock); ?>;
        const bloodGroups = ['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-', 'AB-'];
        
        function updateCounter(element, target) {
            let count = parseInt(element.textContent) || 0;
            const targetValue = parseInt(target) || 0;
            const increment = Math.max(1, Math.floor(targetValue / 100));
            
            const update = () => {
                if (count < targetValue) {
                    count = Math.min(count + increment, targetValue);
                    element.textContent = count;
                    requestAnimationFrame(update);
                } else {
                    element.textContent = targetValue;
                }
            };
            update();
        }

        function fetchData() {
            $.ajax({
                url: 'fetch_data.php', 
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    updateCounter(document.getElementById('total-donors'), response.total_donors || 0);
                    updateCounter(document.getElementById('total-donations'), response.total_donations || 0);
                    updateCounter(document.getElementById('pending-requests'), response.pending_requests || 0);
                    
                    if (response.monthly_donations && Array.isArray(response.monthly_donations)) {
                        monthlyChart.data.datasets[0].data = response.monthly_donations;
                    }
                    if (response.monthly_requests && Array.isArray(response.monthly_requests)) {
                        monthlyChart.data.datasets[1].data = response.monthly_requests;
                    }
                    monthlyChart.update();
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching dashboard data: ", error);
                }
            });

            $.ajax({
                url: 'fetch_blood_stock.php',
                method: 'GET',
                dataType: 'json',
                success: function(stockResponse) {
                    if (stockResponse && stockResponse.blood_groups) {
                        updateCounter(document.getElementById('blood-units'), stockResponse.total_stock || 0);
                        const bloodData = bloodGroups.map(group => stockResponse.blood_groups[group] || 0);
                        bloodAvailabilityChart.data.datasets[0].data = bloodData;
                        bloodAvailabilityChart.update();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching blood stock data: ", error);
                    const bloodData = bloodGroups.map(group => initialBloodStock[group] || 0);
                    bloodAvailabilityChart.data.datasets[0].data = bloodData;
                    bloodAvailabilityChart.update();
                }
            });
        }

        const ctxBlood = document.getElementById('bloodAvailabilityChart').getContext('2d');
        const bloodAvailabilityChart = new Chart(ctxBlood, {
            type: 'pie',
            data: {
                labels: bloodGroups,
                datasets: [{
                    label: 'Blood Units Available',
                    data: bloodGroups.map(group => initialBloodStock[group] || 0),
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#C9CBCF', '#FFCD56'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw + ' units';
                            }
                        }
                    }
                }
            }
        });

        const ctxMonthly = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(ctxMonthly, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [
                    {
                        label: 'Donations',
                        data: [0, 0, 0, 0, 0, 0, 0],
                        backgroundColor: '#36A2EB'
                    },
                    {
                        label: 'Requests',
                        data: [0, 0, 0, 0, 0, 0, 0],
                        backgroundColor: '#FF6384'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        fetchData();
        setInterval(fetchData, 5000);
        
        document.getElementById('logout-btn').addEventListener('click', function() {
            window.location.href = 'logout.php';
        });
    </script>
</body>
</html>