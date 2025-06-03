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
    if (isset($_POST['delete_user'])) {
        $id = $_POST['user_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "User deleted successfully";
    }
    elseif (isset($_POST['update_user'])) {
        $id = $_POST['user_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $blood_group = $_POST['blood_group'];
        $status = $_POST['status'];    
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=?, blood_group=?, status=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $email, $role, $blood_group, $status, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "User updated successfully";
    }
    header("Location: users.php");
    exit();
}
$sql = "SELECT id, name, email, role, blood_group, profile_pic, status FROM users";
$result = $conn->query($sql);
$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
$roleCounts = [];
$bloodGroupCounts = [];
foreach ($users as $user) {
    $roleCounts[$user['role']] = ($roleCounts[$user['role']] ?? 0) + 1;
    $bloodGroupCounts[$user['blood_group']] = ($bloodGroupCounts[$user['blood_group']] ?? 0) + 1;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Blood Bank Management</title>
    <link rel="stylesheet" href="common.css">
    <link rel="stylesheet" href="users.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/glider-js@1/glider.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glider-js@1/glider.min.css">
</head>
<body>
    <header>
        <h1>Blood Bank Management System - User Management</h1>
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
        <a href="handed-over.php"><i class="fas fa-check-circle"></i> Handed Over</a>
        <a href="users.php" class="active"><i class="fas fa-users-cog"></i> Users</a>
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
        <h2><i class="fas fa-users"></i> User Management</h2>
        <div class="filter-search">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="search-bar" placeholder="Search by name, email, or role..." />
            </div>
            <select id="role-filter">
                <option value="">Filter by Role</option>
                <option value="Donor">Donor</option>
                <option value="Recipient">Recipient</option>
                <option value="Admin">Admin</option>
            </select>
            <select id="blood-group-filter">
                <option value="">Filter by Blood Group</option>
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
        <div class="glider-contain">
            <div class="profile-cards-container glider" id="profile-cards-container">
                <?php foreach ($users as $user): ?>
                    <div class="profile-card">
                        <div class="profile-pic">
                            <img src="<?php echo $user['profile_pic'] ?: 'default.jpg'; ?>" alt="User Image">
                            <span class="status <?php echo $user['status'] === 'Online' ? 'online' : 'offline'; ?>"></span>
                        </div>
                        <div class="profile-info">
                            <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                            <p class="role-badge <?php echo strtolower($user['role']); ?>">
                                <i class="fas <?php 
                                    echo $user['role'] === 'Admin' ? 'fa-user-shield' : 
                                        ($user['role'] === 'Donor' ? 'fa-hand-holding-heart' : 'fa-user'); 
                                ?>"></i>
                                <?php echo htmlspecialchars($user['role']); ?>
                            </p>
                            <p class="email" title="<?php echo htmlspecialchars($user['email']); ?>">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                            <p class="blood-group">
                                <i class="fas fa-tint"></i> <?php echo htmlspecialchars($user['blood_group']); ?>
                            </p>
                            <?php if ($_SESSION['role'] === 'Admin'): ?>
                                <div class="user-actions">
                                    <button class="edit-btn" onclick="editUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="glider-prev"><i class="fas fa-chevron-left"></i></button>
            <button class="glider-next"><i class="fas fa-chevron-right"></i></button>
            <div class="glider-dots" id="dots"></div>
        </div>
        <div class="charts">
            <div class="chart">
                <h3><i class="fas fa-chart-pie"></i> User Distribution by Role</h3>
                <canvas id="roleChart"></canvas>
            </div>
            <div class="chart">
                <h3><i class="fas fa-chart-bar"></i> User Distribution by Blood Group</h3>
                <canvas id="bloodGroupChart"></canvas>
            </div>
        </div>
    </div>
    <div id="editModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h3><i class="fas fa-user-edit"></i> Edit User</h3>
            <form method="POST" id="editUserForm">
                <input type="hidden" name="user_id" id="editUserId">
                <div class="form-group">
                    <label for="editName"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" id="editName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="editEmail"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="editRole"><i class="fas fa-user-tag"></i> Role</label>
                    <select id="editRole" name="role" required>
                        <option value="Donor">Donor</option>
                        <option value="Recipient">Recipient</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editBloodGroup"><i class="fas fa-tint"></i> Blood Group</label>
                    <select id="editBloodGroup" name="blood_group" required>
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
                    <label for="editStatus"><i class="fas fa-circle"></i> Status</label>
                    <select id="editStatus" name="status" required>
                        <option value="Online">Online</option>
                        <option value="Offline">Offline</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" name="update_user" class="submit-btn">
                        <i class="fas fa-save"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('logout-btn').addEventListener('click', function() {
            window.location.href = 'logout.php';
        });
        const roleData = <?php echo json_encode($roleCounts); ?>;
        const bloodGroupData = <?php echo json_encode($bloodGroupCounts); ?>;
        
        const roleCtx = document.getElementById('roleChart').getContext('2d');
        const roleChart = new Chart(roleCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(roleData),
                datasets: [{
                    data: Object.values(roleData),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        const bloodGroupCtx = document.getElementById('bloodGroupChart').getContext('2d');
        const bloodGroupChart = new Chart(bloodGroupCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(bloodGroupData),
                datasets: [{
                    label: 'Users by Blood Group',
                    data: Object.values(bloodGroupData),
                    backgroundColor: ['#FF6384', '#9966FF', '#36A2EB', '#FF9F40', '#FFCE56', '#C9CBCF', '#4BC0C0', '#FFCD56'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        let usersData = <?php echo json_encode($users); ?>;
        function editUser(id) {
            const user = usersData.find(u => u.id == id);
            if (user) {
                document.getElementById('editUserId').value = user.id;
                document.getElementById('editName').value = user.name;
                document.getElementById('editEmail').value = user.email;
                document.getElementById('editRole').value = user.role;
                document.getElementById('editBloodGroup').value = user.blood_group;
                document.getElementById('editStatus').value = user.status;
                document.getElementById('editModal').style.display = 'block';
            }
        }
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        function applyFilters() {
            let searchQuery = document.getElementById('search-bar').value.toLowerCase();
            let selectedRole = document.getElementById('role-filter').value;
            let selectedBloodGroup = document.getElementById('blood-group-filter').value;
            let filteredUsers = usersData.filter(user => {
                return (
                    (searchQuery === '' || 
                     user.name.toLowerCase().includes(searchQuery) || 
                     user.email.toLowerCase().includes(searchQuery)) &&
                    (selectedRole === '' || user.role === selectedRole) &&
                    (selectedBloodGroup === '' || user.blood_group === selectedBloodGroup)
                );
            });
            renderUsers(filteredUsers);
        }
        function renderUsers(users) {
            let profileCards = '';
            let roleCounts = {};
            let bloodGroupCounts = {};
            users.forEach(user => {
                let userImage = user.profile_pic && user.profile_pic !== '' ? user.profile_pic : 'default.jpg';
                profileCards += `
                    <div class="profile-card">
                        <div class="profile-pic">
                            <img src="${userImage}" alt="User Image" onerror="this.onerror=null;this.src='default.jpg';">
                            <span class="status ${user.status === 'Online' ? 'online' : 'offline'}"></span>
                        </div>
                        <div class="profile-info">
                            <h3>${user.name}</h3>
                            <p class="role-badge ${user.role.toLowerCase()}">
                                <i class="fas ${user.role === 'Admin' ? 'fa-user-shield' : 
                                    (user.role === 'Donor' ? 'fa-hand-holding-heart' : 'fa-user')}"></i>
                                ${user.role}
                            </p>
                            <p class="email" title="${user.email}">
                                <i class="fas fa-envelope"></i> ${user.email}
                            </p>
                            <p class="blood-group">
                                <i class="fas fa-tint"></i> ${user.blood_group}
                            </p>
                            <?php if ($_SESSION['role'] === 'Admin'): ?>
                                <div class="user-actions">
                                    <button class="edit-btn" onclick="editUser(${user.id})">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="user_id" value="${user.id}">
                                        <button type="submit" name="delete_user" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                `;
                roleCounts[user.role] = (roleCounts[user.role] || 0) + 1;
                bloodGroupCounts[user.blood_group] = (bloodGroupCounts[user.blood_group] || 0) + 1;
            });
            document.getElementById('profile-cards-container').innerHTML = profileCards;
            updateCharts(roleCounts, bloodGroupCounts);
            initGlider();
        }
        function updateCharts(roleData, bloodGroupData) {
            roleChart.data.labels = Object.keys(roleData);
            roleChart.data.datasets[0].data = Object.values(roleData);
            roleChart.update();
            bloodGroupChart.data.labels = Object.keys(bloodGroupData);
            bloodGroupChart.data.datasets[0].data = Object.values(bloodGroupData);
            bloodGroupChart.update();
        }
        function initGlider() {
            new Glider(document.querySelector('.profile-cards-container'), {
                slidesToShow: 1,
                slidesToScroll: 1,
                draggable: true,
                dots: '#dots',
                arrows: {
                    prev: '.glider-prev',
                    next: '.glider-next'
                },
                responsive: [
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                    },
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3
                        }
                    }
                ]
            });
        }
        document.getElementById('search-bar').addEventListener('input', applyFilters);
        document.getElementById('role-filter').addEventListener('change', applyFilters);
        document.getElementById('blood-group-filter').addEventListener('change', applyFilters);
        
        document.querySelectorAll('.close-notification').forEach(btn => {
            btn.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeModal();
            }
        }
        document.addEventListener("DOMContentLoaded", function() {
            initGlider();
        });
    </script>
</body>
</html>