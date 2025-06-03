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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_stock'])) {
        $blood_group = $_POST['blood_group'];
        $quantity = $_POST['quantity'];
        $expiry_date = $_POST['expiry_date'];
        
        $stmt = $conn->prepare("INSERT INTO blood_stock (blood_group, quantity, expiry_date) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $blood_group, $quantity, $expiry_date);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['update_stock'])) {
        $id = $_POST['stock_id'];
        $quantity = $_POST['quantity'];
        $expiry_date = $_POST['expiry_date'];
        
        $stmt = $conn->prepare("UPDATE blood_stock SET quantity=?, expiry_date=? WHERE id=?");
        $stmt->bind_param("isi", $quantity, $expiry_date, $id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete_stock'])) {
        $id = $_POST['stock_id'];
        
        $stmt = $conn->prepare("DELETE FROM blood_stock WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

$inventory = [];
$result = $conn->query("SELECT * FROM blood_stock ORDER BY blood_group, expiry_date");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $inventory[] = $row;
    }
}

$blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
$group_totals = [];
foreach ($blood_groups as $group) {
    $result = $conn->query("SELECT SUM(quantity) as total FROM blood_stock WHERE blood_group='$group'");
    $row = $result->fetch_assoc();
    $group_totals[$group] = $row['total'] ?? 0;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory - Blood Bank Management</title>
    <link rel="stylesheet" href="common.css">
    <link rel="stylesheet" href="admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <header>
        <h1>Blood Bank Management System - Blood Inventory</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['email'] ?? 'User'); ?></span>
            <button id="logout-btn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>
    </header>
    
    <div class="sidebar">
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="donors.php"><i class="fas fa-user-friends"></i> Donors</a>
        <a href="blood-donation.php"><i class="fas fa-tint"></i> Blood Donation</a>
        <a href="requests.php"><i class="fas fa-hand-holding-heart"></i> Requests</a>
        <a href="handed-over.php"><i class="fas fa-check-circle"></i> Handed Over</a>
        <a href="users.php"><i class="fas fa-users-cog"></i> Users</a>
        <a href="inventory.php" class="active"><i class="fas fa-warehouse"></i> Blood Inventory</a>
        <?php if ($_SESSION['role'] === 'Admin'): ?>
            <a href="admin_dashboard.php"><i class="fas fa-user-shield"></i> Admin Dashboard</a>
        <?php endif; ?>
    </div>
    
    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="notification success">
                <?php echo $_SESSION['message']; ?>
                <span class="close-notification">&times;</span>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <h2><i class="fas fa-warehouse"></i> Blood Inventory Management</h2>
        
        <div class="admin-stats">
            <?php foreach ($group_totals as $group => $total): ?>
            <div class="stat-box">
                <i class="fas fa-tint blood-group-<?php echo strtolower($group); ?>"></i>
                <p class="counter"><?php echo $total; ?></p>
                <span><?php echo $group; ?> Stock</span>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="management-section">
            <div class="section-header">
                <h3><i class="fas fa-plus-circle"></i> Add New Stock</h3>
            </div>
            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label for="blood_group">Blood Group:</label>
                        <select id="blood_group" name="blood_group" required>
                            <option value="">Select Blood Group</option>
                            <?php foreach ($blood_groups as $group): ?>
                                <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity (ml):</label>
                        <input type="number" id="quantity" name="quantity" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date:</label>
                        <input type="date" id="expiry_date" name="expiry_date" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="add_stock" class="submit-btn">Add to Inventory</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="management-section">
            <div class="section-header">
                <h3><i class="fas fa-list"></i> Current Inventory</h3>
                <div class="search-filter">
                    <input type="text" id="inventory-search" placeholder="Search inventory...">
                    <select id="blood-group-filter">
                        <option value="">All Blood Groups</option>
                        <?php foreach ($blood_groups as $group): ?>
                            <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="table-responsive scrollable-table">
                <table class="management-table">
                    <thead>
                        <tr>
                            <th>Blood Group</th>
                            <th>Quantity (ml)</th>
                            <th>Expiry Date</th>
                            <th>Days Remaining</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $item): 
                            $expiry_date = new DateTime($item['expiry_date']);
                            $today = new DateTime();
                            $days_remaining = $today->diff($expiry_date)->days;
                            $days_remaining = $expiry_date > $today ? $days_remaining : -$days_remaining;
                        ?>
                        <tr class="<?php echo $days_remaining < 0 ? 'expired' : ($days_remaining < 14 ? 'expiring-soon' : ''); ?>">
                            <td><?php echo $item['blood_group']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo $expiry_date->format('Y-m-d'); ?></td>
                            <td>
                                <?php if ($days_remaining < 0): ?>
                                    <span class="status-badge expired">Expired <?php echo abs($days_remaining); ?> days ago</span>
                                <?php else: ?>
                                    <span class="status-badge <?php echo $days_remaining < 14 ? 'warning' : 'success'; ?>">
                                        <?php echo $days_remaining; ?> days
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="action-buttons">
                                <button class="edit-btn" onclick="showEditForm(<?php echo $item['id']; ?>, '<?php echo $item['blood_group']; ?>', <?php echo $item['quantity']; ?>, '<?php echo $item['expiry_date']; ?>')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="stock_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="delete_stock" class="delete-btn" onclick="return confirm('Are you sure you want to delete this stock entry?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div id="editForm" class="modal-form" style="display:none;">
            <div class="form-container">
                <h3><i class="fas fa-edit"></i> Edit Stock</h3>
                <form method="POST">
                    <input type="hidden" id="edit_stock_id" name="stock_id">
                    
                    <div class="form-group">
                        <label for="edit_blood_group">Blood Group:</label>
                        <input type="text" id="edit_blood_group" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_quantity">Quantity (ml):</label>
                        <input type="number" id="edit_quantity" name="quantity" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_expiry_date">Expiry Date:</label>
                        <input type="date" id="edit_expiry_date" name="expiry_date" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="update_stock" class="submit-btn">Update Stock</button>
                        <button type="button" onclick="hideEditForm()" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="chart-container">
            <h3><i class="fas fa-chart-pie"></i> Inventory Overview</h3>
            <canvas id="inventoryChart"></canvas>
        </div>
    </div>

    <script>
        let inventoryChart;
        
        $(document).ready(function() {
            const ctx = document.getElementById('inventoryChart').getContext('2d');
            inventoryChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_keys($group_totals)); ?>,
                    datasets: [{
                        label: 'Blood Stock (ml)',
                        data: <?php echo json_encode(array_values($group_totals)); ?>,
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                            '#9966FF', '#FF9F40', '#8AC249', '#FFCD56'
                        ],
                        borderColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                            '#9966FF', '#FF9F40', '#8AC249', '#FFCD56'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Units (ml)'
                            }
                        }
                    }
                }
            });
            
            $('#logout-btn').click(function() {
                window.location.href = 'logout.php';
            });
            
            $('.close-notification').click(function() {
                $(this).parent().fadeOut();
            });
            
            $('#inventory-search, #blood-group-filter').on('input change', function() {
                filterInventory();
            });
            
            $('#expiry_date, #edit_expiry_date').attr('min', new Date().toISOString().split('T')[0]);
        });
        
        function filterInventory() {
            const searchTerm = $('#inventory-search').val().toLowerCase();
            const bloodGroupFilter = $('#blood-group-filter').val();
            
            $('.management-section:has(h3:contains("Current Inventory")) table tbody tr').each(function() {
                const bloodGroup = $(this).find('td:eq(0)').text().trim();
                const quantity = $(this).find('td:eq(1)').text().toLowerCase();
                const expiryDate = $(this).find('td:eq(2)').text().toLowerCase();
                
                const bloodGroupMatch = bloodGroupFilter === '' || bloodGroup === bloodGroupFilter;
                const searchMatch = bloodGroup.toLowerCase().includes(searchTerm) || 
                                   quantity.includes(searchTerm) || 
                                   expiryDate.includes(searchTerm);
                
                if (bloodGroupMatch && searchMatch) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        
        function showEditForm(id, bloodGroup, quantity, expiryDate) {
            $('#edit_stock_id').val(id);
            $('#edit_blood_group').val(bloodGroup);
            $('#edit_quantity').val(quantity);
            $('#edit_expiry_date').val(expiryDate);
            $('#editForm').show();
        }
        
        function hideEditForm() {
            $('#editForm').hide();
        }
    </script>
</body>
</html>