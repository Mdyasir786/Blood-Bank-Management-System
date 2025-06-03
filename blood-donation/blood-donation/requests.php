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

$sql = "SELECT * FROM requests";
$result = $conn->query($sql);
$requests = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests - Blood Bank Management</title>
    <link rel="stylesheet" href="common.css">
    <link rel="stylesheet" href="requests.css">
    <link rel="stylesheet" href="chatbot.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/glider-js@1/glider.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glider-js@1/glider.min.css">
</head>
<body>
    <header>
        <h1>Blood Bank Management System - Request Management</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <button id="logout-btn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>
    </header>
    
    <div class="sidebar">
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="donors.php"><i class="fas fa-user-friends"></i> Donors</a>
        <a href="blood-donation.php"><i class="fas fa-tint"></i> Blood Donation</a>
        <a href="requests.php" class="active"><i class="fas fa-hand-holding-heart"></i> Requests</a>
        <a href="handed-over.php"><i class="fas fa-check-circle"></i> Handed Over</a>
        <?php if ($_SESSION['role'] === 'Admin'): ?>
            <a href="users.php"><i class="fas fa-users-cog"></i> Users</a>
            <a href="admin_dashboard.php"><i class="fas fa-user-shield"></i> Admin Dashboard</a>
        <?php endif; ?>
    </div>
    
    <div class="container">
        <h2><i class="fas fa-hand-holding-heart"></i> Blood Requests</h2>
        <div class="request-stats">
            <div class="stat-box">
                <i class="fas fa-clipboard-list"></i>
                <p class="counter" data-target="<?php echo count($requests); ?>">0</p>
                <span>Total Requests</span>
            </div>
            <div class="stat-box">
                <i class="fas fa-clock"></i>
                <p class="counter" data-target="<?php echo count(array_filter($requests, function($r) { return $r['status'] === 'Pending'; })); ?>">0</p>
                <span>Pending Requests</span>
            </div>
            <div class="stat-box">
                <i class="fas fa-check-circle"></i>
                <p class="counter" data-target="<?php echo count(array_filter($requests, function($r) { return $r['status'] === 'Approved'; })); ?>">0</p>
                <span>Fulfilled Requests</span>
            </div>
        </div>
        <div class="filter-search-container">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="search-bar" placeholder="Search by patient name or blood group...">
            </div>
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
            <select id="status-filter">
                <option value="">Filter by Status</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
            <select id="urgency-filter">
                <option value="">Filter by Urgency</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
        </div>
        <div class="glider-contain">
            <div class="request-cards-container glider" id="request-cards-container">
                <?php foreach ($requests as $request): ?>
                    <div class="request-card">
                        <div class="request-header">
                            <h3><?php echo $request['patient_name']; ?></h3>
                            <span class="urgency-badge <?php echo strtolower($request['urgency']); ?>"><?php echo $request['urgency']; ?> Priority</span>
                        </div>
                        <p><strong>Blood Group:</strong> <span class="blood-group <?php echo strtolower($request['blood_group']); ?>"><?php echo $request['blood_group']; ?></span></p>
                        <p><strong>Hospital:</strong> <?php echo $request['hospital']; ?></p>
                        <p><strong>Status:</strong> <span class="status <?php echo strtolower($request['status']); ?>"><?php echo $request['status']; ?></span></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="glider-prev"><i class="fas fa-chevron-left"></i></button>
            <button class="glider-next"><i class="fas fa-chevron-right"></i></button>
            <div class="glider-dots" id="dots"></div>
        </div>
        
        <button id="open-request-modal" class="add-request-btn">Add New Request</button>
        
        <div class="table-container">
            <h3><i class="fas fa-table"></i> Request Records</h3>
            <div class="table-scroll">
                <table id="request-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Patient Name</th>
                            <th>Blood Group</th>
                            <th>Hospital</th>
                            <th>Status</th>
                            <th>Urgency</th>
                        </tr>
                    </thead>
                    <tbody id="request-table-body">
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo $request['date']; ?></td>
                                <td><?php echo $request['patient_name']; ?></td>
                                <td><span class="blood-group <?php echo strtolower($request['blood_group']); ?>"><?php echo $request['blood_group']; ?></span></td>
                                <td><?php echo $request['hospital']; ?></td>
                                <td><span class="status <?php echo strtolower($request['status']); ?>"><?php echo $request['status']; ?></span></td>
                                <td><span class="urgency-badge <?php echo strtolower($request['urgency']); ?>"><?php echo $request['urgency']; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="request-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3><i class="fas fa-plus-square"></i> Add New Request</h3>
            <form id="request-form" method="post" action="add_request.php">
                <div class="form-group">
                    <label for="patient-name"><i class="fas fa-user"></i> Patient Name</label>
                    <input type="text" id="patient-name" name="patient-name" required>
                </div>
                <div class="form-group">
                    <label for="gender"><i class="fas fa-venus-mars"></i> Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="blood-group"><i class="fas fa-tint"></i> Blood Group</label>
                    <select id="blood-group" name="blood-group" required>
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
                    <label for="city"><i class="fas fa-city"></i> City:</label>
                    <input type="text" id="city" name="city" required>
                </div>
                <div class="form-group">
                    <label for="contact"><i class="fas fa-phone"></i> Contact Number:</label>
                    <input type="tel" id="contact" name="contact" required>
                </div>
                <div class="form-group">
                    <label for="hospital"><i class="fas fa-hospital"></i> Hospital</label>
                    <input type="text" id="hospital" name="hospital" required>
                </div>
                <div class="form-group">
                    <label for="urgency"><i class="fas fa-exclamation-triangle"></i> Urgency</label>
                    <select id="urgency" name="urgency" required>
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Submit</button>
                    <button type="button" id="cancel-btn" class="cancel-btn"><i class="fas fa-times"></i> Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <?php include 'includes/chatbot.php'; ?>
    <script>
    document.getElementById('logout-btn').addEventListener('click', function() {
        window.location.href = 'logout.php';
    });

    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        let count = 0;
        const increment = target / 100;
        const updateCounter = () => {
            if (count < target) {
                count += increment;
                counter.innerText = Math.ceil(count);
                setTimeout(updateCounter, 10);
            } else {
                counter.innerText = target;
            }
        };
        updateCounter();
    });

    const modal = document.getElementById('request-modal');
    const openBtn = document.getElementById('open-request-modal');
    const closeBtn = document.querySelector('.close-btn');
    const cancelBtn = document.getElementById('cancel-btn');

    openBtn.addEventListener('click', function() {
        modal.style.display = 'block';
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    });

    function closeModal() {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.getElementById('request-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        
        fetch('add_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Request added successfully!');
                closeModal();
                location.reload();
            } else {
                alert("Error: " + (data.error || 'Unknown error occurred'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting the form. Please try again.');
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const searchBar = document.getElementById("search-bar");
        const bloodGroupFilter = document.getElementById("blood-group-filter");
        const statusFilter = document.getElementById("status-filter");
        const urgencyFilter = document.getElementById("urgency-filter");

        function filterRequests() {
            const searchQuery = searchBar.value.toLowerCase();
            const bloodGroupValue = bloodGroupFilter.value;
            const statusValue = statusFilter.value;
            const urgencyValue = urgencyFilter.value;

            document.querySelectorAll(".request-card, #request-table tbody tr").forEach((element) => {
                const patientName = element.querySelector("h3")?.innerText.toLowerCase() || element.cells[1].innerText.toLowerCase();
                const bloodGroup = element.querySelector(".blood-group")?.innerText || element.cells[2].innerText;
                const status = element.querySelector(".status")?.innerText || element.cells[4].innerText;
                const urgency = element.querySelector(".urgency-badge")?.innerText || element.cells[5].innerText;

                const matchesSearch = searchQuery === "" || patientName.includes(searchQuery);
                const matchesBloodGroup = bloodGroupValue === "" || bloodGroup === bloodGroupValue;
                const matchesStatus = statusValue === "" || status === statusValue;
                const matchesUrgency = urgencyValue === "" || urgency.includes(urgencyValue);

                if (matchesSearch && matchesBloodGroup && matchesStatus && matchesUrgency) {
                    element.style.display = "";
                } else {
                    element.style.display = "none";
                }
            });
        }

        searchBar.addEventListener("input", filterRequests);
        bloodGroupFilter.addEventListener("change", filterRequests);
        statusFilter.addEventListener("change", filterRequests);
        urgencyFilter.addEventListener("change", filterRequests);

        new Glider(document.querySelector('.request-cards-container'), {
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
    });
    </script>
</body>
</html>