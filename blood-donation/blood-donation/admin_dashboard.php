<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$host = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "blood-donation";
$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_donor'])) {
        $id = $_POST['donor_id'];
        $stmt = $conn->prepare("DELETE FROM donors WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['message'] = "Donor deleted successfully";
        } else {
            $_SESSION['message'] = "Error preparing statement: " . $conn->error;
        }
    }
    elseif (isset($_POST['update_donor'])) {
        $id = $_POST['donor_id'];
        $name = $_POST['name'];
        $blood_group = $_POST['blood_group'];
        $gender = $_POST['gender'];
        $city = $_POST['city'];
        $contact = $_POST['contact'];
        $last_donation = $_POST['last_donation'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE donors SET name=?, blood_group=?, gender=?, city=?, contact=?, last_donation=?, status=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("sssssssi", $name, $blood_group, $gender, $city, $contact, $last_donation, $status, $id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['message'] = "Donor updated successfully";
        } else {
            $_SESSION['message'] = "Error preparing statement: " . $conn->error;
        }
    }
    elseif (isset($_POST['add_donor'])) {
        $name = $_POST['name'];
        $blood_group = $_POST['blood_group'];
        $gender = $_POST['gender'];
        $city = $_POST['city'];
        $contact = $_POST['contact'];
        $last_donation = $_POST['last_donation'];
        $stmt = $conn->prepare("INSERT INTO donors (name, blood_group, gender, city, contact, last_donation, status) VALUES (?, ?, ?, ?, ?, ?, 'Available')");
        if ($stmt) {
            $stmt->bind_param("ssssss", $name, $blood_group, $gender, $city, $contact, $last_donation);
            $stmt->execute();
            $stmt->close();
            $_SESSION['message'] = "Donor added successfully";
        } else {
            $_SESSION['message'] = "Error preparing statement: " . $conn->error;
        }
    }
    elseif (isset($_POST['add_donation'])) {
        $donor_id = $_POST['donor_id'];
        $blood_group = $_POST['blood_group'];
        $quantity = $_POST['quantity'];
        $donation_date = $_POST['donation_date'];
        $checkStmt = $conn->prepare("SELECT name FROM donors WHERE id = ?");
        if ($checkStmt) {
            $checkStmt->bind_param("i", $donor_id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            if ($result->num_rows > 0) {
                $donor = $result->fetch_assoc();
                $donor_name = $donor['name'];
                $stmt = $conn->prepare("INSERT INTO donations (donor_name, blood_group, date, status) VALUES (?, ?, ?, 'Completed')");
                if ($stmt) {
                    $stmt->bind_param("sss", $donor_name, $blood_group, $donation_date);
                    if ($stmt->execute()) {
                        $updateStmt = $conn->prepare("UPDATE donors SET last_donation = ? WHERE id = ?");
                        if ($updateStmt) {
                            $updateStmt->bind_param("si", $donation_date, $donor_id);
                            $updateStmt->execute();
                            $updateStmt->close();
                        }
                        $stockStmt = $conn->prepare("INSERT INTO blood_stock (blood_group, quantity) VALUES (?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
                        if ($stockStmt) {
                            $stockStmt->bind_param("sd", $blood_group, $quantity);
                            $stockStmt->execute();
                            $stockStmt->close();
                        }
                        $_SESSION['message'] = "Donation added successfully";
                    } else {
                        $_SESSION['message'] = "Error executing donation statement: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $_SESSION['message'] = "Error preparing donation statement: " . $conn->error;
                }
            } else {
                $_SESSION['message'] = "Error: Donor not found";
            }
            $checkStmt->close();
        } else {
            $_SESSION['message'] = "Error checking donor: " . $conn->error;
        }
    }
    elseif (isset($_POST['delete_donation'])) {
        $id = $_POST['donation_id'];
        $stmt = $conn->prepare("DELETE FROM donations WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['message'] = "Donation record deleted successfully";
        } else {
            $_SESSION['message'] = "Error preparing statement: " . $conn->error;
        }
    }
    elseif (isset($_POST['update_request'])) {
        $id = $_POST['request_id'];
        $patient_name = $_POST['patient_name'];
        $blood_group = $_POST['blood_group'];
        $hospital = $_POST['hospital'];
        $status = $_POST['status'];
        $urgency = $_POST['urgency'];
        $stmt = $conn->prepare("UPDATE requests SET patient_name=?, blood_group=?, hospital=?, status=?, urgency=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("sssssi", $patient_name, $blood_group, $hospital, $status, $urgency, $id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['message'] = "Request updated successfully";
        } else {
            $_SESSION['message'] = "Error preparing statement: " . $conn->error;
        }
    }
    elseif (isset($_POST['delete_request'])) {
        $id = $_POST['request_id'];
        $stmt = $conn->prepare("DELETE FROM requests WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['message'] = "Request deleted successfully";
        } else {
            $_SESSION['message'] = "Error preparing statement: " . $conn->error;
        }
    }
    elseif (isset($_POST['add_request'])) {
        $patient_name = $_POST['patient_name'];
        $gender = $_POST['gender'];
        $blood_group = $_POST['blood_group'];
        $city = $_POST['city'];
        $contact = $_POST['contact'];
        $hospital = $_POST['hospital'];
        $urgency = $_POST['urgency'];
        $stmt = $conn->prepare("INSERT INTO requests (patient_name, gender, blood_group, city, contact, hospital, urgency, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
        if ($stmt) {
            $stmt->bind_param("sssssss", $patient_name, $gender, $blood_group, $city, $contact, $hospital, $urgency);
            $stmt->execute();
            $stmt->close();
            $_SESSION['message'] = "Request added successfully";
        } else {
            $_SESSION['message'] = "Error preparing statement: " . $conn->error;
        }
    }
    elseif (isset($_POST['add_handed_over'])) {
        $request_id = $_POST['request_id'];
        $quantity = $_POST['quantity'];
        $handover_date = $_POST['handover_date'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("INSERT INTO handed_over (request_id, quantity, handover_date, status) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("idss", $request_id, $quantity, $handover_date, $status);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Handed over record added successfully";
                
                $updateStmt = $conn->prepare("UPDATE requests SET status='Completed' WHERE id=?");
                if ($updateStmt) {
                    $updateStmt->bind_param("i", $request_id);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
            } else {
                $_SESSION['message'] = "Error executing statement: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = "Error preparing statement: " . $conn->error;
        }
    }
    elseif (isset($_POST['update_handed_over'])) {
        $id = $_POST['handed_over_id'];
        $request_id = $_POST['request_id'];
        $quantity = $_POST['quantity'];
        $handover_date = $_POST['handover_date'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE handed_over SET request_id=?, quantity=?, handover_date=?, status=? WHERE handover_id=?");
        if ($stmt) {
            $stmt->bind_param("idssi", $request_id, $quantity, $handover_date, $status, $id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['message'] = "Handed over record updated successfully";
        } else {
            $_SESSION['message'] = "Error preparing statement: " . $conn->error;
        }
    }
    elseif (isset($_POST['delete_handed_over'])) {
        $id = $_POST['handed_over_id'];
        $stmt = $conn->prepare("DELETE FROM handed_over WHERE handover_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['message'] = "Handed over record deleted successfully";
        } else {
            $_SESSION['message'] = "Error preparing statement: " . $conn->error;
        }
    }
    
    header("Location: admin_dashboard.php");
    exit();
}
function getAdminStats($conn) {
    $stats = array(
        'total_donors' => 0,
        'total_donations' => 0,
        'pending_requests' => 0,
        'blood_units' => 0,
        'total_users' => 0,
        'completed_requests' => 0,
        'blood_stock' => array_fill(0, 8, 0)
    );
    $result = $conn->query("SELECT COUNT(*) as count FROM donors");
    if ($result) $stats['total_donors'] = $result->fetch_assoc()['count'];
    $result = $conn->query("SELECT COUNT(*) as count FROM donations");
    if ($result) $stats['total_donations'] = $result->fetch_assoc()['count'];
    $result = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status='Pending'");
    if ($result) $stats['pending_requests'] = $result->fetch_assoc()['count'];
    $result = $conn->query("SELECT SUM(quantity) as total FROM blood_stock");
    if ($result) $stats['blood_units'] = $result->fetch_assoc()['total'] ?? 0;
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) $stats['total_users'] = $result->fetch_assoc()['count'];
    $result = $conn->query("SELECT COUNT(*) as count FROM handed_over");
    if ($result) $stats['completed_requests'] = $result->fetch_assoc()['count'];
    $blood_groups = array('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-');
    foreach ($blood_groups as $index => $group) {
        $result = $conn->query("SELECT SUM(quantity) as total FROM blood_stock WHERE blood_group='$group'");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['blood_stock'][$index] = $row['total'] ?? 0;
        }
    }
    return $stats;
}
function getDonors($conn) {
    $donors = array();
    $result = $conn->query("SELECT * FROM donors ORDER BY name");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $donors[] = $row;
        }
    }
    return $donors;
}
function getDonations($conn) {
    $donations = array();
    $result = $conn->query("SELECT * FROM donations ORDER BY date DESC LIMIT 10");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['formatted_date'] = (!empty($row['date']) && ($row['date'] != '0000-00-00')) ? 
                date('M d, Y', strtotime($row['date'])) : 'N/A';
            $donations[] = $row;
        }
    }
    return $donations;
}
function getRequests($conn) {
    $requests = array();
    $result = $conn->query("SELECT * FROM requests ORDER BY date DESC LIMIT 10");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['formatted_date'] = (!empty($row['date']) && ($row['date'] != '0000-00-00')) ? 
                date('M d, Y', strtotime($row['date'])) : 'N/A';
            $requests[] = $row;
        }
    }
    return $requests;
}
function getHandedOver($conn) {
    $handedOver = array();
    $result = $conn->query("
        SELECT handover_id, request_id, quantity, handover_date, status
        FROM handed_over
        ORDER BY handover_date DESC 
        LIMIT 10
    ");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['formatted_handover_date'] = (!empty($row['handover_date']) && ($row['handover_date'] != '0000-00-00')) ? 
                date('M d, Y', strtotime($row['handover_date'])) : 'N/A';
            $handedOver[] = $row;
        }
    }
    return $handedOver;
}
$stats = getAdminStats($conn);
$donors = getDonors($conn);
$donations = getDonations($conn);
$requests = getRequests($conn);
$handedOver = getHandedOver($conn);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Blood Bank Management</title>
    <link rel="stylesheet" href="common.css">
    <link rel="stylesheet" href="admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <h1>Blood Bank Management System - Admin Dashboard</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <button id="logout-btn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>
    </header>
    <div class="sidebar">
        <a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="donors.php"><i class="fas fa-user-friends"></i> Manage Donors</a>
        <a href="blood-donation.php"><i class="fas fa-tint"></i> Manage Donations</a>
        <a href="requests.php"><i class="fas fa-hand-holding-heart"></i> Manage Requests</a>
        <a href="handed-over.php"><i class="fas fa-check-circle"></i> Completed Requests</a>
        <a href="users.php"><i class="fas fa-users-cog"></i> Manage Users</a>
        <a href="inventory.php"><i class="fas fa-warehouse"></i> Blood Inventory</a>
    </div>
    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="notification success">
                <?php echo $_SESSION['message']; ?>
                <span class="close-notification">&times;</span>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard Overview</h2>
        <div class="admin-stats">
            <div class="stat-box">
                <i class="fas fa-user-friends"></i>
                <p class="counter" id="total-donors"><?php echo $stats['total_donors']; ?></p>
                <span>Total Donors</span>
            </div>
            <div class="stat-box">
                <i class="fas fa-tint"></i>
                <p class="counter" id="total-donations"><?php echo $stats['total_donations']; ?></p>
                <span>Total Donations</span>
            </div>
            <div class="stat-box">
                <i class="fas fa-hand-holding-heart"></i>
                <p class="counter" id="pending-requests"><?php echo $stats['pending_requests']; ?></p>
                <span>Pending Requests</span>
            </div>
            <div class="stat-box">
                <i class="fas fa-warehouse"></i>
                <p class="counter" id="blood-units"><?php echo $stats['blood_units']; ?></p>
                <span>Blood Units Available</span>
            </div>
            <div class="stat-box">
                <i class="fas fa-users"></i>
                <p class="counter" id="total-users"><?php echo $stats['total_users']; ?></p>
                <span>System Users</span>
            </div>
            <div class="stat-box">
                <i class="fas fa-check-circle"></i>
                <p class="counter" id="completed-requests"><?php echo $stats['completed_requests']; ?></p>
                <span>Completed Requests</span>
            </div>
        </div>
        <div class="admin-actions">
            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            <div class="action-buttons">
                <button onclick="window.location.href='add_user.php'">
                    <i class="fas fa-user-plus"></i> Add New User
                </button>
                <button onclick="showAddDonorForm()">
                    <i class="fas fa-user-plus"></i> Add New Donor
                </button>
                <button onclick="showAddDonationForm()">
                    <i class="fas fa-tint"></i> Add New Donation
                </button>
                <button onclick="showAddRequestForm()">
                    <i class="fas fa-hand-holding-heart"></i> Add New Request
                </button>
                <button onclick="showAddHandedOverForm()">
                    <i class="fas fa-exchange-alt"></i> Add Handed Over
                </button>
                <button onclick="window.location.href='inventory.php'">
                    <i class="fas fa-warehouse"></i> Blood Inventory
                </button>
            </div>
        </div>
        <div class="management-section">
            <div class="section-header">
                <h3><i class="fas fa-user-friends"></i> Donor Management</h3>
                <div class="search-filter">
                    <input type="text" id="donor-search" placeholder="Search donors...">
                    <select id="blood-group-filter">
                        <option value="">All Blood Groups</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                    </select>
                    <select id="status-filter">
                        <option value="">All Statuses</option>
                        <option value="Available">Available</option>
                        <option value="Unavailable">Unavailable</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive scrollable-table">
                <table class="management-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Blood Group</th>
                            <th>Gender</th>
                            <th>City</th>
                            <th>Contact</th>
                            <th>Last Donation</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donors as $donor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($donor['name']); ?></td>
                            <td><span class="blood-group"><?php echo htmlspecialchars($donor['blood_group']); ?></span></td>
                            <td><?php echo htmlspecialchars($donor['gender']); ?></td>
                            <td><?php echo htmlspecialchars($donor['city']); ?></td>
                            <td><?php echo htmlspecialchars($donor['contact']); ?></td>
                            <td><?php echo $donor['last_donation'] ? date('M d, Y', strtotime($donor['last_donation'])) : 'Never'; ?></td>
                            <td>
                                <span class="status-badge <?php echo strtolower($donor['status']); ?>">
                                    <?php echo htmlspecialchars($donor['status']); ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <button class="edit-btn" onclick="editDonor(<?php echo $donor['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="donor_id" value="<?php echo $donor['id']; ?>">
                                    <button type="submit" name="delete_donor" class="delete-btn" onclick="return confirm('Are you sure you want to delete this donor?')">
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
        <div class="management-section">
            <div class="section-header">
                <h3><i class="fas fa-tint"></i> Recent Donations</h3>
                <div class="search-filter">
                    <input type="text" id="donation-search" placeholder="Search donations...">
                    <select id="donation-blood-group-filter">
                        <option value="">All Blood Groups</option>
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
            </div>
            <div class="table-responsive scrollable-table">
                <table class="management-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Donor</th>
                            <th>Blood Group</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $donation): ?>
                        <tr>
                            <td><?php echo $donation['formatted_date']; ?></td>
                            <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                            <td><span class="blood-group"><?php echo htmlspecialchars($donation['blood_group']); ?></span></td>
                            <td>
                                <span class="status-badge <?php echo strtolower($donation['status']); ?>">
                                    <?php echo htmlspecialchars($donation['status']); ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="donation_id" value="<?php echo $donation['id']; ?>">
                                    <button type="submit" name="delete_donation" class="delete-btn" onclick="return confirm('Are you sure you want to delete this donation record?')">
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
        <div class="management-section">
            <div class="section-header">
                <h3><i class="fas fa-hand-holding-heart"></i> Recent Requests</h3>
                <div class="search-filter">
                    <input type="text" id="request-search" placeholder="Search requests...">
                    <select id="request-blood-group-filter">
                        <option value="">All Blood Groups</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                    </select>
                    <select id="request-status-filter">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive scrollable-table">
                <table class="management-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Blood Group</th>
                            <th>Hospital</th>
                            <th>Status</th>
                            <th>Urgency</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo $request['formatted_date']; ?></td>
                            <td><?php echo htmlspecialchars($request['patient_name']); ?></td>
                            <td><span class="blood-group"><?php echo htmlspecialchars($request['blood_group']); ?></span></td>
                            <td><?php echo htmlspecialchars($request['hospital']); ?></td>
                            <td>
                                <span class="status-badge <?php echo strtolower($request['status']); ?>">
                                    <?php echo htmlspecialchars($request['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="urgency-badge <?php echo strtolower($request['urgency']); ?>">
                                    <?php echo htmlspecialchars($request['urgency']); ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <button class="edit-btn" onclick="editRequest(<?php echo $request['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" name="delete_request" class="delete-btn" onclick="return confirm('Are you sure you want to delete this request?')">
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
        <div class="management-section">
            <div class="section-header">
                <h3><i class="fas fa-exchange-alt"></i> Recent Handed Over Records</h3>
                <div class="search-filter">
                    <input type="text" id="handed-over-search" placeholder="Search records...">
                    <select id="handed-over-status-filter">
                        <option value="">All Statuses</option>
                        <option value="Delivered">Delivered</option>
                        <option value="In Transit">In Transit</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive scrollable-table">
                <table class="management-table">
                    <thead>
                        <tr>
                            <th>Handover ID</th>
                            <th>Request ID</th>
                            <th>Quantity (ml)</th>
                            <th>Handover Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($handedOver)): ?>
                            <?php foreach ($handedOver as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['handover_id']); ?></td>
                                <td><?php echo htmlspecialchars($record['request_id']); ?></td>
                                <td><?php echo htmlspecialchars($record['quantity']); ?></td>
                                <td><?php echo $record['formatted_handover_date']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $record['status'])); ?>">
                                        <?php echo htmlspecialchars($record['status']); ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <button class="edit-btn" onclick="editHandedOver(<?php echo $record['handover_id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="handed_over_id" value="<?php echo $record['handover_id']; ?>">
                                        <button type="submit" name="delete_handed_over" class="delete-btn" onclick="return confirm('Are you sure you want to delete this record?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-records">No handed over records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="addDonorForm" class="modal-form" style="display:none;">
            <div class="form-container">
                <h3>Add New Donor</h3>
                <form method="POST" id="addDonorFormElement">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Full Name" required>
                    </div>
                    <div class="form-group">
                        <label for="blood_group">Blood Group</label>
                        <select id="blood_group" name="blood_group" required>
                            <option value="">Select Blood Group</option>
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
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" placeholder="City" required>
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact Number</label>
                        <input type="tel" id="contact" name="contact" placeholder="Contact Number" required>
                    </div>
                    <div class="form-group">
                        <label for="last_donation">Last Donation Date (if any)</label>
                        <input type="date" id="last_donation" name="last_donation">
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="add_donor" class="submit-btn">Add Donor</button>
                        <button type="button" onclick="hideAddDonorForm()" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="addDonationForm" class="modal-form" style="display:none;">
            <div class="form-container">
                <h3>Add New Donation</h3>
                <form method="POST" id="addDonationFormElement">
                    <div class="form-group">
                        <label for="donor_id">Donor</label>
                        <select id="donor_id" name="donor_id" required>
                            <option value="">Select Donor</option>
                            <?php foreach ($donors as $donor): ?>
                                <option value="<?php echo $donor['id']; ?>"><?php echo htmlspecialchars($donor['name']); ?> (<?php echo htmlspecialchars($donor['blood_group']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="blood_group">Blood Group</label>
                        <select id="blood_group" name="blood_group" required>
                            <option value="">Select Blood Group</option>
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
                        <label for="quantity">Quantity (ml)</label>
                        <input type="number" id="quantity" name="quantity" placeholder="Quantity" required>
                    </div>
                    <div class="form-group">
                        <label for="donation_date">Donation Date</label>
                        <input type="date" id="donation_date" name="donation_date" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="add_donation" class="submit-btn">Add Donation</button>
                        <button type="button" onclick="hideAddDonationForm()" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="editDonorForm" class="modal-form" style="display:none;">
            <div class="form-container">
                <h3>Edit Donor</h3>
                <form method="POST" id="editDonorFormElement">
                    <input type="hidden" name="donor_id" id="editDonorId">
                    <div class="form-group">
                        <label for="editDonorName">Full Name</label>
                        <input type="text" id="editDonorName" name="name" placeholder="Full Name" required>
                    </div>
                    <div class="form-group">
                        <label for="editDonorBloodGroup">Blood Group</label>
                        <select id="editDonorBloodGroup" name="blood_group" required>
                            <option value="">Select Blood Group</option>
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
                        <label for="editDonorGender">Gender</label>
                        <select id="editDonorGender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editDonorCity">City</label>
                        <input type="text" id="editDonorCity" name="city" placeholder="City" required>
                    </div>
                    <div class="form-group">
                        <label for="editDonorContact">Contact Number</label>
                        <input type="tel" id="editDonorContact" name="contact" placeholder="Contact Number" required>
                    </div>
                    <div class="form-group">
                        <label for="editDonorLastDonation">Last Donation Date</label>
                        <input type="date" id="editDonorLastDonation" name="last_donation">
                    </div>
                    <div class="form-group">
                        <label for="editDonorStatus">Status</label>
                        <select id="editDonorStatus" name="status" required>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_donor" class="submit-btn">Update Donor</button>
                        <button type="button" onclick="hideEditDonorForm()" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="addRequestForm" class="modal-form" style="display:none;">
            <div class="form-container">
                <h3>Add New Request</h3>
                <form method="POST" id="addRequestFormElement">
                    <div class="form-group">
                        <label for="patient_name">Patient Name</label>
                        <input type="text" id="patient_name" name="patient_name" required>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="blood_group">Blood Group</label>
                        <select id="blood_group" name="blood_group" required>
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
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact Number</label>
                        <input type="tel" id="contact" name="contact" required>
                    </div>
                    <div class="form-group">
                        <label for="hospital">Hospital</label>
                        <input type="text" id="hospital" name="hospital" required>
                    </div>
                    <div class="form-group">
                        <label for="urgency">Urgency</label>
                        <select id="urgency" name="urgency" required>
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="add_request" class="submit-btn">Add Request</button>
                        <button type="button" onclick="hideAddRequestForm()" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="editRequestForm" class="modal-form" style="display:none;">
            <div class="form-container">
                <h3>Edit Request</h3>
                <form method="POST" id="editRequestFormElement">
                    <input type="hidden" name="request_id" id="editRequestId">
                    <div class="form-group">
                        <label for="editPatientName">Patient Name</label>
                        <input type="text" id="editPatientName" name="patient_name" required>
                    </div>
                    <div class="form-group">
                        <label for="editGender">Gender</label>
                        <select id="editGender" name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editBloodGroup">Blood Group</label>
                        <select id="editBloodGroup" name="blood_group" required>
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
                    <div class="form-group">
                        <label for="editCity">City</label>
                        <input type="text" id="editCity" name="city" required>
                    </div>
                    <div class="form-group">
                        <label for="editContact">Contact Number</label>
                        <input type="tel" id="editContact" name="contact" required>
                    </div>
                    <div class="form-group">
                        <label for="editHospital">Hospital</label>
                        <input type="text" id="editHospital" name="hospital" required>
                    </div>
                    <div class="form-group">
                        <label for="editUrgency">Urgency</label>
                        <select id="editUrgency" name="urgency" required>
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editStatus">Status</label>
                        <select id="editStatus" name="status" required>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_request" class="submit-btn">Update Request</button>
                        <button type="button" onclick="hideEditRequestForm()" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="addHandedOverForm" class="modal-form" style="display:none;">
            <div class="form-container">
                <h3>Add Handed Over Record</h3>
                <form method="POST" id="addHandedOverFormElement">
                    <div class="form-group">
                        <label for="request_id">Request ID</label>
                        <input type="number" id="request_id" name="request_id" placeholder="Request ID" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity (ml)</label>
                        <input type="number" id="quantity" name="quantity" placeholder="Quantity" required>
                    </div>
                    <div class="form-group">
                        <label for="handover_date">Handover Date</label>
                        <input type="date" id="handover_date" name="handover_date" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="Delivered">Delivered</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="add_handed_over" class="submit-btn">Add Record</button>
                        <button type="button" onclick="hideAddHandedOverForm()" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="editHandedOverForm" class="modal-form" style="display:none;">
            <div class="form-container">
                <h3>Edit Handed Over Record</h3>
                <form method="POST" id="editHandedOverFormElement">
                    <input type="hidden" name="handed_over_id" id="editHandedOverId">
                    <div class="form-group">
                        <label for="editRequestId">Request ID</label>
                        <input type="number" id="editRequestId" name="request_id" placeholder="Request ID" required>
                    </div>
                    <div class="form-group">
                        <label for="editQuantity">Quantity (ml)</label>
                        <input type="number" id="editQuantity" name="quantity" placeholder="Quantity" required>
                    </div>
                    <div class="form-group">
                        <label for="editHandoverDate">Handover Date</label>
                        <input type="date" id="editHandoverDate" name="handover_date" required>
                    </div>
                    <div class="form-group">
                        <label for="editHandedOverStatus">Status</label>
                        <select id="editHandedOverStatus" name="status" required>
                            <option value="Delivered">Delivered</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_handed_over" class="submit-btn">Update Record</button>
                        <button type="button" onclick="hideEditHandedOverForm()" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="chart-container">
            <h3><i class="fas fa-chart-pie"></i> Blood Stock Levels</h3>
            <canvas id="bloodStockChart"></canvas>
        </div>
        </div>                           
    <script>
        let bloodStockChart;
        let donorsData = <?php echo json_encode($donors); ?>;
        let requestsData = <?php echo json_encode($requests); ?>;
        let handedOverData = <?php echo json_encode($handedOver); ?>;
        $(document).ready(function() {
            initializeBloodStockChart();
            fetchAdminStats();
            fetchRecentActivity();
            setInterval(fetchAdminStats, 30000);
            setInterval(fetchRecentActivity, 60000);
            $('#logout-btn').click(function() {
                window.location.href = 'logout.php';
            });
            $('.close-notification').click(function() {
                $(this).parent().fadeOut();
            });
            $('body').on('input', '#donor-search, #blood-group-filter, #status-filter', filterDonors);
            $('body').on('input', '#donation-search, #donation-blood-group-filter', filterDonations);
            $('body').on('input', '#request-search, #request-blood-group-filter, #request-status-filter', filterRequests);
            $('body').on('input', '#handed-over-search, #handed-over-status-filter', filterHandedOver);
        });
        function showAddDonationForm() {
            $('#addDonationForm').show();
        }
        function hideAddDonationForm() {
            $('#addDonationForm').hide();
        }
        function filterDonors() {
            const searchTerm = $('#donor-search').val().toLowerCase();
            const bloodGroupFilter = $('#blood-group-filter').val();
            const statusFilter = $('#status-filter').val();
            $('.management-section:has(h3:contains("Donor Management")) table tbody tr').each(function() {
                const name = $(this).find('td:eq(0)').text().toLowerCase();
                const bloodGroup = $(this).find('td:eq(1)').text().trim();
                const status = $(this).find('.status-badge').text().trim();
                const nameMatch = name.includes(searchTerm);
                const bloodGroupMatch = bloodGroupFilter === '' || bloodGroup === bloodGroupFilter;
                const statusMatch = statusFilter === '' || status === statusFilter;
                if (nameMatch && bloodGroupMatch && statusMatch) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        function filterDonations() {
            const searchTerm = $('#donation-search').val().toLowerCase();
            const bloodGroupFilter = $('#donation-blood-group-filter').val();
            $('.management-section:has(h3:contains("Recent Donations")) table tbody tr').each(function() {
                const donorName = $(this).find('td:eq(1)').text().toLowerCase();
                const bloodGroup = $(this).find('td:eq(2)').text().trim();
                const nameMatch = donorName.includes(searchTerm);
                const bloodGroupMatch = bloodGroupFilter === '' || bloodGroup === bloodGroupFilter;
                if (nameMatch && bloodGroupMatch) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        function filterRequests() {
            const searchTerm = $('#request-search').val().toLowerCase();
            const bloodGroupFilter = $('#request-blood-group-filter').val();
            const statusFilter = $('#request-status-filter').val();
            $('.management-section:has(h3:contains("Recent Requests")) table tbody tr').each(function() {
                const patientName = $(this).find('td:eq(1)').text().toLowerCase();
                const bloodGroup = $(this).find('td:eq(2)').text().trim();
                const status = $(this).find('.status-badge').text().trim();
                const nameMatch = patientName.includes(searchTerm);
                const bloodGroupMatch = bloodGroupFilter === '' || bloodGroup === bloodGroupFilter;
                const statusMatch = statusFilter === '' || status === statusFilter;
                if (nameMatch && bloodGroupMatch && statusMatch) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        function filterHandedOver() {
            const searchTerm = $('#handed-over-search').val().toLowerCase();
            const statusFilter = $('#handed-over-status-filter').val();
            $('.management-section:has(h3:contains("Recent Handed Over Records")) table tbody tr').each(function() {
                const requestId = $(this).find('td:eq(1)').text().toLowerCase();
                const status = $(this).find('.status-badge').text().trim();
                const idMatch = requestId.includes(searchTerm);
                const statusMatch = statusFilter === '' || status === statusFilter;
                if (idMatch && statusMatch) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        function showAddDonorForm() {
            $('#addDonorForm').show();
        }
        function hideAddDonorForm() {
            $('#addDonorForm').hide();
        }
        function editDonor(id) {
            const donor = donorsData.find(d => d.id == id);
            if (donor) {
                $('#editDonorId').val(donor.id);
                $('#editDonorName').val(donor.name);
                $('#editDonorBloodGroup').val(donor.blood_group);
                $('#editDonorGender').val(donor.gender);
                $('#editDonorCity').val(donor.city);
                $('#editDonorContact').val(donor.contact);
                $('#editDonorLastDonation').val(donor.last_donation);
                $('#editDonorStatus').val(donor.status);
                $('#editDonorForm').show();
            } else {
                alert('Error loading donor data');
            }
        }
        function hideEditDonorForm() {
            $('#editDonorForm').hide();
        }
        
        function showAddRequestForm() {
            $('#addRequestForm').show();
        }
        
        function hideAddRequestForm() {
            $('#addRequestForm').hide();
        }
        
        function editRequest(id) {
            const request = requestsData.find(r => r.id == id);
            if (request) {
                $('#editRequestId').val(request.id);
                $('#editPatientName').val(request.patient_name);
                $('#editGender').val(request.gender);
                $('#editBloodGroup').val(request.blood_group);
                $('#editCity').val(request.city);
                $('#editContact').val(request.contact);
                $('#editHospital').val(request.hospital);
                $('#editUrgency').val(request.urgency);
                $('#editStatus').val(request.status);
                $('#editRequestForm').show();
            } else {
                alert('Error loading request data');
            }
        }
        
        function hideEditRequestForm() {
            $('#editRequestForm').hide();
        }
        
        function showAddHandedOverForm() {
            $('#addHandedOverForm').show();
        }
        
        function hideAddHandedOverForm() {
            $('#addHandedOverForm').hide();
        }
        
        function editHandedOver(id) {
            const record = handedOverData.find(h => h.handover_id == id);
            if (record) {
                $('#editHandedOverId').val(record.handover_id);
                $('#editRequestId').val(record.request_id);
                $('#editQuantity').val(record.quantity);
                $('#editHandoverDate').val(record.handover_date);
                $('#editHandedOverStatus').val(record.status);
                $('#editHandedOverForm').show();
            } else {
                alert('Error loading handed over record data');
            }
        }
        
        function hideEditHandedOverForm() {
            $('#editHandedOverForm').hide();
        }
        
        function initializeBloodStockChart() {
            const ctx = document.getElementById('bloodStockChart').getContext('2d');
            bloodStockChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'],
                    datasets: [{
                        label: 'Units Available',
                        data: <?php echo json_encode($stats['blood_stock']); ?>,
                        backgroundColor: '#e74c3c',
                        borderColor: '#c0392b',
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
                                text: 'Units'
                            }
                        }
                    }
                }
            });
        }
        
        function fetchAdminStats() {
            $.ajax({
                url: 'fetch_admin_stats.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('.counter').each(function() {
                        const id = $(this).attr('id');
                        if (response[id] !== undefined) {
                            animateCounter(id, response[id]);
                        }
                    });
                    
                    if (bloodStockChart && response.blood_stock) {
                        bloodStockChart.data.datasets[0].data = response.blood_stock;
                        bloodStockChart.update();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching admin stats:", error);
                }
            });
        }
        
        function fetchRecentActivity() {
            $.ajax({
                url: 'fetch_recent_activity.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    let activityHtml = '';
                    if (response.length > 0) {
                        response.forEach(activity => {
                            activityHtml += `
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="${getActivityIcon(activity.type)}"></i>
                                    </div>
                                    <div class="activity-details">
                                        <p class="activity-description">${activity.description}</p>
                                        <p class="activity-time">${activity.timestamp}</p>
                                    </div>
                                    ${activity.action_needed ? '<span class="action-needed">Action Needed</span>' : ''}
                                </div>
                            `;
                        });
                    } else {
                        activityHtml = `
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="activity-details">
                                    <p class="activity-description">No recent activity found</p>
                                    <p class="activity-time">Just now</p>
                                </div>
                            </div>
                        `;
                    }
                    $('#activity-list').html(activityHtml);
                },
                error: function() {
                    $('#activity-list').html(`
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="activity-details">
                                <p class="activity-description">Failed to load recent activity</p>
                                <p class="activity-time">Just now</p>
                            </div>
                        </div>
                    `);
                }
            });
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
        function getActivityIcon(type) {
            switch(type) {
                case 'donation': return 'fas fa-tint';
                case 'request': return 'fas fa-hand-holding-heart';
                case 'user': return 'fas fa-user';
                case 'system': return 'fas fa-cog';
                default: return 'fas fa-info-circle';
            }
        }
    </script>
</body>
</html>