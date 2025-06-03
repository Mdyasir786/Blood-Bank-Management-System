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

$sql = "SELECT * FROM donors";
$result = $conn->query($sql);
$donors = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $donors[] = $row;
    }
}

$cityQuery = "SELECT DISTINCT city FROM donors";
$cityResult = $conn->query($cityQuery);
$cities = [];
while ($cityRow = $cityResult->fetch_assoc()) {
    $cities[] = $cityRow['city'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donors - Blood Bank Management</title>
    <link rel="stylesheet" href="common.css">
    <link rel="stylesheet" href="donors.css">
    <link rel="stylesheet" href="chatbot.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/glider-js@1/glider.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glider-js@1/glider.min.css">
</head>
<body>
    <header>
        <h1>Blood Bank Management System - Donor Management</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <button id="logout-btn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>
    </header>
    
    <div class="sidebar">
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="donors.php" class="active"><i class="fas fa-user-friends"></i> Donors</a>
        <a href="blood-donation.php"><i class="fas fa-tint"></i> Blood Donation</a>
        <a href="requests.php"><i class="fas fa-hand-holding-heart"></i> Requests</a>
        <a href="handed-over.php"><i class="fas fa-check-circle"></i> Handed Over</a>
        <?php if ($_SESSION['role'] === 'Admin'): ?>
            <a href="users.php"><i class="fas fa-users-cog"></i> Users</a>
            <a href="admin_dashboard.php"><i class="fas fa-user-shield"></i> Admin Dashboard</a>
        <?php endif; ?>
    </div>
    
    <div class="container">
        <h2><i class="fas fa-user-friends"></i> Donors</h2>
        
        <div class="stats-container">
            <div class="stat-box">
                <i class="fas fa-users"></i>
                <p class="counter" data-target="<?php echo count($donors); ?>">0</p>
                <span>Total Donors</span>
            </div>
            <div class="stat-box">
                <i class="fas fa-tint"></i>
                <p>O+</p>
                <span>Most Common Blood Group</span>
            </div>
        </div>
        
        <div class="filter-search-container">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="search-bar" placeholder="Search by name, blood group, or location...">
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
            <select id="gender-filter">
                <option value="">Filter by Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            <select id="city-filter">
                <option value="">Filter by City</option>
                <?php foreach ($cities as $city): ?>
                    <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="glider-contain">
            <div class="donor-cards-container glider" id="donor-cards-container">
                <?php foreach ($donors as $donor): ?>
                    <div class="donor-card">
                        <div class="donor-photo">
                            <i class="fas fa-user-circle"></i>
                            <?php if ($donor['donation_count'] > 3): ?>
                                <span class="badge">Frequent Donor</span>
                            <?php endif; ?>
                        </div>
                        <div class="donor-info">
                            <h3><?php echo $donor['name']; ?></h3>
                            <p><strong>Blood Group:</strong> <span class="blood-group <?php echo strtolower($donor['blood_group']); ?>"><?php echo $donor['blood_group']; ?></span></p>
                            <p><strong>Gender:</strong> <?php echo $donor['gender']; ?></p>
                            <p><strong>City:</strong> <?php echo $donor['city']; ?></p>
                            <p><strong>Status:</strong> <span class="status <?php echo ($donor['status'] === 'Available') ? 'available' : 'unavailable'; ?>"><?php echo $donor['status']; ?></span></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="glider-prev"><i class="fas fa-chevron-left"></i></button>
            <button class="glider-next"><i class="fas fa-chevron-right"></i></button>
            <div class="glider-dots" id="dots"></div>
        </div>
        
        <button id="donate-now-btn" class="add-donor-btn">Donate Now</button>
        
        <div class="table-container">
            <h3><i class="fas fa-table"></i> Donor Records</h3>
            <div class="table-scroll">
                <table id="donor-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Blood Group</th>
                            <th>Gender</th>
                            <th>City</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="donor-table-body">
                        <?php foreach ($donors as $donor): ?>
                            <tr>
                                <td><?php echo $donor['name']; ?></td>
                                <td><span class="blood-group <?php echo strtolower($donor['blood_group']); ?>"><?php echo $donor['blood_group']; ?></span></td>
                                <td><?php echo $donor['gender']; ?></td>
                                <td><?php echo $donor['city']; ?></td>
                                <td><span class="status <?php echo ($donor['status'] === 'Available') ? 'available' : 'unavailable'; ?>"><?php echo $donor['status']; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="donate-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3><i class="fas fa-user-plus"></i> Donor Details</h3>
            <form id="donate-form-submit">
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" id="name" name="name" required>
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
                    <label for="gender"><i class="fas fa-venus-mars"></i> Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="city"><i class="fas fa-city"></i> City</label>
                    <input type="text" id="city" name="city" required>
                </div>
                <div class="form-group">
                    <label for="contact"><i class="fas fa-phone"></i> Contact Number</label>
                    <input type="tel" id="contact" name="contact" required>
                </div>
                <div class="form-group">
                    <label for="last-donation"><i class="fas fa-calendar"></i> Last Donation Date</label>
                    <input type="date" id="last-donation" name="last-donation">
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

    const modal = document.getElementById('donate-modal');
    const openBtn = document.getElementById('donate-now-btn');
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

    document.getElementById('donate-form-submit').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        
        fetch('add_donor.php', {
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
                alert('Donor added successfully!');
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
        const genderFilter = document.getElementById("gender-filter");
        const cityFilter = document.getElementById("city-filter");

        function filterDonors() {
            const searchQuery = searchBar.value.toLowerCase();
            const bloodGroupValue = bloodGroupFilter.value;
            const genderValue = genderFilter.value;
            const cityValue = cityFilter.value;

            document.querySelectorAll(".donor-card, #donor-table tbody tr").forEach((element) => {
                const donorName = element.querySelector("h3")?.innerText.toLowerCase() || element.cells[0].innerText.toLowerCase();
                const bloodGroup = element.querySelector(".blood-group")?.innerText || element.cells[1].innerText;
                const gender = element.querySelector("p:nth-child(3)")?.innerText.split(": ")[1] || element.cells[2].innerText;
                const city = element.querySelector("p:nth-child(4)")?.innerText.split(": ")[1] || element.cells[3].innerText;

                const matchesSearch = searchQuery === "" || 
                    donorName.includes(searchQuery) || 
                    bloodGroup.toLowerCase().includes(searchQuery) || 
                    city.toLowerCase().includes(searchQuery);

                const matchesBloodGroup = bloodGroupValue === "" || bloodGroup === bloodGroupValue;
                const matchesGender = genderValue === "" || gender === genderValue;
                const matchesCity = cityValue === "" || city === cityValue;

                if (matchesSearch && matchesBloodGroup && matchesGender && matchesCity) {
                    element.style.display = "";
                } else {
                    element.style.display = "none";
                }
            });
        }

        searchBar.addEventListener("input", filterDonors);
        bloodGroupFilter.addEventListener("change", filterDonors);
        genderFilter.addEventListener("change", filterDonors);
        cityFilter.addEventListener("change", filterDonors);

        new Glider(document.querySelector('.donor-cards-container'), {
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