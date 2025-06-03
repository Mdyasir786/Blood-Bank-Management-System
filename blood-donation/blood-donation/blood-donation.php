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
    <title>Blood Donation - Blood Bank Management</title>
    <link rel="stylesheet" href="common.css">
    <link rel="stylesheet" href="blood-donation.css">
    <link rel="stylesheet" href="chatbot.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <header>
        <h1>Blood Bank Management System - Blood Donation</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <button id="logout-btn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>
    </header>
    
    <div class="sidebar">
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="donors.php"><i class="fas fa-user-friends"></i> Donors</a>
        <a href="blood-donation.php" class="active"><i class="fas fa-tint"></i> Blood Donation</a>
        <a href="requests.php"><i class="fas fa-hand-holding-heart"></i> Requests</a>
        <a href="handed-over.php"><i class="fas fa-check-circle"></i> Handed Over</a>
        <?php if ($_SESSION['role'] === 'Admin'): ?>
            <a href="users.php"><i class="fas fa-users-cog"></i> Users</a>
            <a href="admin_dashboard.php"><i class="fas fa-user-shield"></i> Admin Dashboard</a>
        <?php endif; ?>
    </div>
    
    <div class="container">
        <h2><i class="fas fa-tint"></i> Blood Donation</h2>
        
        <div class="donation-stats">
            <div class="stat-box">
                <i class="fas fa-hand-holding-medical"></i>
                <p class="counter" id="total-donations">0</p>
                <span>Total Donations</span>
            </div>
            <div class="stat-box">
                <i class="fas fa-flask"></i>
                <p class="counter" id="blood-stock"><?php echo $totalStock; ?></p>
                <span>Total Blood Units</span>
            </div>
            <div class="stat-box">
                <div id="blood-group-summary">
                    <p><i class="fas fa-tint blood-aplus"></i> A+: <span id="aplus-count"><?php echo $bloodStock['A+']; ?></span></p>
                    <p><i class="fas fa-tint blood-bplus"></i> B+: <span id="bplus-count"><?php echo $bloodStock['B+']; ?></span></p>
                    <p><i class="fas fa-tint blood-oplus"></i> O+: <span id="oplus-count"><?php echo $bloodStock['O+']; ?></span></p>
                    <p><i class="fas fa-tint blood-abplus"></i> AB+: <span id="abplus-count"><?php echo $bloodStock['AB+']; ?></span></p>
                    <p><i class="fas fa-tint blood-aminus"></i> A-: <span id="aminus-count"><?php echo $bloodStock['A-']; ?></span></p>
                    <p><i class="fas fa-tint blood-bminus"></i> B-: <span id="bminus-count"><?php echo $bloodStock['B-']; ?></span></p>
                    <p><i class="fas fa-tint blood-ominus"></i> O-: <span id="ominus-count"><?php echo $bloodStock['O-']; ?></span></p>
                    <p><i class="fas fa-tint blood-abminus"></i> AB-: <span id="abminus-count"><?php echo $bloodStock['AB-']; ?></span></p>
                </div>
            </div>
        </div>

        <div class="filter-search-container">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="search-bar" placeholder="Search by donor name or blood group...">
            </div>
            <select id="date-range-filter">
                <option value="">Filter by Date Range</option>
                <option value="last-week">Last Week</option>
                <option value="last-month">Last Month</option>
                <option value="last-year">Last Year</option>
            </select>
            <select id="blood-group-filter">
                <option value="">Filter by Blood Group</option>
                <option value="A+">A+</option>
                <option value="B+">B+</option>
                <option value="O+">O+</option>
                <option value="AB+">AB+</option>
                <option value="A-">A-</option>
                <option value="B-">B-</option>
                <option value="O-">O-</option>
                <option value="AB-">AB-</option>
            </select>
        </div>

        <div class="table-container">
            <h3><i class="fas fa-table"></i> Donation Records</h3>
            <div class="table-scroll">
                <table id="donation-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Donor Name</th>
                            <th>Blood Group</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="donation-table-body"></tbody>
                </table>
            </div>
        </div>

        <div class="charts-container">
            <div class="chart-box">
                <h3><i class="fas fa-chart-pie"></i> Blood Group Distribution</h3>
                <canvas id="bloodGroupChart"></canvas>
            </div>
            <div class="chart-box">
                <h3><i class="fas fa-chart-line"></i> Monthly Donations</h3>
                <canvas id="monthlyDonationsChart"></canvas>
            </div>
        </div>
    </div>
    <?php include 'includes/chatbot.php'; ?>
    <script>
    document.getElementById('logout-btn').addEventListener('click', function () {
        window.location.href = 'logout.php';
    });

    let bloodGroupChart;
    let monthlyDonationsChart;
    let initialBloodStock = {
        total: <?php echo $totalStock; ?>,
        groups: <?php echo json_encode($bloodStock); ?>
    };

    function fetchData() {
        let search = $('#search-bar').val();
        let bloodGroup = $('#blood-group-filter').val();
        let dateRange = $('#date-range-filter').val();

        $.ajax({
            url: 'fetch_donations.php',
            method: 'GET',
            data: { search, blood_group: bloodGroup, date_range: dateRange },
            dataType: 'json',
            success: function (response) {
                if (response && Array.isArray(response)) {
                    updateTable(response);
                    animateCounter('total-donations', response.length);
                    updateCharts(response);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching donations:", error);
            }
        });

        $.ajax({
            url: 'fetch_blood_stock.php',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response && response.blood_groups) {
                    const totalStock = response.total_stock || 0;
                    const bloodGroups = response.blood_groups || {};
                    animateCounter('blood-stock', totalStock);
                    updateBloodGroupCounts(bloodGroups);
                    initialBloodStock = {
                        total: totalStock,
                        groups: bloodGroups
                    };
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching blood stock:", error);
                animateCounter('blood-stock', initialBloodStock.total);
                updateBloodGroupCounts(initialBloodStock.groups);
            }
        });
    }

    function updateBloodGroupCounts(bloodGroups) {
        $('#aplus-count').text(bloodGroups['A+'] || 0);
        $('#bplus-count').text(bloodGroups['B+'] || 0);
        $('#oplus-count').text(bloodGroups['O+'] || 0);
        $('#abplus-count').text(bloodGroups['AB+'] || 0);
        $('#aminus-count').text(bloodGroups['A-'] || 0);
        $('#bminus-count').text(bloodGroups['B-'] || 0);
        $('#ominus-count').text(bloodGroups['O-'] || 0);
        $('#abminus-count').text(bloodGroups['AB-'] || 0);
    }

    function animateCounter(id, endValue) {
        const element = document.getElementById(id);
        const startValue = parseInt(element.textContent) || 0;
        const duration = 1000; 
        const steps = 60;
        const stepValue = (endValue - startValue) / steps;

        let current = startValue;
        let count = 0;

        const interval = setInterval(() => {
            current += stepValue;
            element.textContent = Math.floor(current);

            count++;
            if (count >= steps || Math.floor(current) >= endValue) {
                element.textContent = endValue; 
                clearInterval(interval);
            }
        }, duration / steps);
    }

    function updateTable(data) {
        let tableHTML = "";
        if (data && Array.isArray(data)) {
            data.forEach(donation => {
                tableHTML += `
                    <tr>
                        <td>${donation.date || ''}</td>
                        <td>${donation.donor_name || ''}</td>
                        <td>${donation.blood_group || ''}</td>
                        <td>${donation.status || ''}</td>
                    </tr>
                `;
            });
        }
        $('#donation-table-body').html(tableHTML);
    }

    function updateCharts(data) {
        let bloodGroupCounts = {};
        let monthlyCounts = new Array(12).fill(0);

        if (data && Array.isArray(data)) {
            data.forEach(d => {
                if (d.blood_group) {
                    bloodGroupCounts[d.blood_group] = (bloodGroupCounts[d.blood_group] || 0) + 1;
                }
                if (d.date) {
                    try {
                        let month = new Date(d.date).getMonth();
                        monthlyCounts[month]++;
                    } catch (e) {
                        console.error("Invalid date format:", d.date);
                    }
                }
            });
        }

        if (bloodGroupChart) bloodGroupChart.destroy();
        bloodGroupChart = new Chart(document.getElementById('bloodGroupChart'), {
            type: 'pie',
            data: {
                labels: Object.keys(bloodGroupCounts),
                datasets: [{
                    data: Object.values(bloodGroupCounts),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4CAF50', '#FF9800', '#9C27B0']
                }]
            }
        });

        if (monthlyDonationsChart) monthlyDonationsChart.destroy();
        monthlyDonationsChart = new Chart(document.getElementById('monthlyDonationsChart'), {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Donations',
                    data: monthlyCounts,
                    backgroundColor: '#36A2EB'
                }]
            }
        });
    }

    $(document).ready(() => {
        updateBloodGroupCounts(initialBloodStock.groups);
        fetchData();
        setInterval(fetchData, 60000);
        $('#search-bar, #date-range-filter, #blood-group-filter').on('input change', fetchData);
    });
    </script>
</body>
</html>