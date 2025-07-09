<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "guidancehub";

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Query to get the number of appointments
$sql_appointments = "SELECT COUNT(id) as total_appointments FROM appointments";
$result_appointments = $con->query($sql_appointments);
$total_appointments = $result_appointments->fetch_assoc()['total_appointments'];

// Query to get the number of referrals
$sql_referrals = "SELECT COUNT(id) as total_referrals FROM referrals";
$result_referrals = $con->query($sql_referrals);
$total_referrals = $result_referrals->fetch_assoc()['total_referrals'];

// Query to get the number of assessments
$sql_assessments = "SELECT COUNT(id) as total_assessments FROM assessments";
$result_assessments = $con->query($sql_assessments);
$total_assessments = $result_assessments->fetch_assoc()['total_assessments'];

// Fetch announcements
$announcements = [];
$sql = "SELECT * FROM announcement ORDER BY published_at DESC";
$result = $con->query($sql);
if ($result) {
    $announcements = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Error fetching announcements: " . $con->error);
}

// Check if logout is requested
if (isset($_GET['logout'])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: /index.php"); // Redirect to the login page after logout
    exit;
}

// Close the database connection
$con->close();
?>

<!doctype html>
<html>
<head>
<title> GuidanceHub | Dashboard </title>
    <link rel="icon" type="images/x-icon" href="/src/images/UMAK-CGCS-logo.png" />
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css"  rel="stylesheet" />
        <script src="https://kit.fontawesome.com/95c10202b4.js" crossorigin="anonymous"></script>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="./output.css" rel="stylesheet">   
    <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Montserrat:wght@500&display=swap" rel="stylesheet"> 
    <style>
        .marcellus-regular {
            font-family: "Marcellus", serif;
            font-style: normal;
            letter-spacing: 2px; }
        body::-webkit-scrollbar {
            width: 15px; }
        body::-webkit-scrollbar-track {
            background: #f1f1f1; }
        body::-webkit-scrollbar-thumb {
            background: #888; }
        body::-webkit-scrollbar-thumb:hover {
            background: #555; }
        .blue-dark {
            background-color: #111c4e; }
        .blue-1:hover {
            color: #111c4e; }
        .blue-2:hover {
            color: #618dc2;}

    </style>
</head>
<body class="bg-gray-100">

<!-- TOP NAVIGATION BAR -->
<header class="fixed top-0 left-0 z-50 w-full shadow-md blue-dark marcellus-regular">
    <div class="flex px-3 py-4 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between w-full mx-auto max-w-7xl">

            <!-- LOGO -->
            <div class="flex items-center mx-5">
                <img src="/src/images/UMAK-CGCS-logo.png" alt="CGCS Logo" class="w-10 h-auto md:w-14">
                <span class="ml-4 font-semibold tracking-widest text-white md:text-2xl">GuidanceHub</span>
            </div>

            <!-- Right-side icons -->
            <div class="flex items-center gap-4">

                <!-- Hamburger Icon (Mobile) -->
                <button id="menu-toggle" class="text-2xl text-white md:hidden focus:outline-none">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <!-- Messages Icon -->
                <div class="relative">
                    <button id="messageButton" class="text-white hover:text-gray-300 focus:outline-none">
                        <i class="text-2xl fa-solid fa-message"></i>
                        <span id="messageBadge" class="absolute hidden w-4 h-4 text-xs font-bold text-white bg-red-500 rounded-full -top-1 -right-1">3</span>
                    </button>
                </div>

                <!-- Notifications Icon -->
                <div class="relative">
                    <button id="notificationButton" class="text-white hover:text-gray-300 focus:outline-none">
                        <i class="text-2xl fa-solid fa-bell"></i>
                        <span id="notificationBadge" class="absolute hidden w-4 h-4 text-xs font-bold text-white bg-red-500 rounded-full -top-1 -right-1">0</span>
                    </button>
                </div>

                <!-- Logout Button -->
                <a href="?logout=true" class="text-white hover:text-gray-300">
                    <i class="text-xl fa-solid fa-right-from-bracket"></i>
                </a>

            </div>

        </div>
    </div>
</header>

<!-- SIDE NAVIGATION MENU -->
<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform bg-white border-r border-gray-300 sm:translate-x-0" aria-label="Sidebar">
    <div class="h-full px-3 pb-4 mt-5 overflow-y-auto">
        <ul class="m-3 space-y-2 font-medium">
            <li><a href="dashboard.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group"><i class="w-5 h-5 text-gray-500 fa-solid fa-house group-hover:text-gray-900"></i><span class="ms-3">Dashboard</span></a></li>
            <li><a href="appointment.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group"><i class="w-5 h-5 text-gray-500 fa-solid fa-calendar-check group-hover:text-gray-900"></i><span class="ms-3">Appointment</span></a></li>
            <li><a href="referral.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group"><i class="w-5 h-5 text-gray-500 fa-solid fa-paper-plane group-hover:text-gray-900"></i><span class="ms-3">Referral</span></a></li>
            <li><a href="assessments.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group"><i class="w-5 h-5 text-gray-500 fa-solid fa-file-lines group-hover:text-gray-900"></i><span class="ms-3">Assessments</span></a></li>
            <li><a href="report.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group"><i class="w-5 h-5 text-gray-500 fa-solid fa-chart-pie group-hover:text-gray-900"></i><span class="ms-3">Report & Analytic</span></a></li>
            <li><a href="?logout=true" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group"><i class="w-5 h-5 text-gray-500 fa-solid fa-right-from-bracket group-hover:text-gray-900"></i><span class="ms-3">Log Out</span></a></li>
        </ul>
    </div>
</aside>

<!-- MAIN CONTENT -->
<div class="flex flex-col min-h-screen px-4 pt-20 ml-0 transition-all duration-300 sm:ml-64">
    <section class="mb-8">
        <h2 class="p-3 m-5 text-4xl font-bold tracking-tight">
            <?php echo "Welcome, " . $_SESSION['name'] . "!<br>" ?>
        </h2>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="p-6 bg-white rounded-lg shadow">
                <h2 class="text-2xl font-semibold text-gray-700">Appointment</h2>
                    <p class="mt-4 text-4xl font-bold text-green-500" id="total-appointments"><?php echo $total_appointments;?></p>
                    <p class="mt-2 text-sm text-gray-500">total sessions</p>
            </div>
            <div class="p-6 bg-white rounded-lg shadow">
                <h2 class="text-2xl font-semibold text-gray-700">Referral</h2>
                    <p class="mt-4 text-4xl font-bold text-blue-500" id="total-referrals"><?php echo $total_referrals;?></p>
                    <p class="mt-2 text-sm text-gray-500">number of students</p>
            </div>
            <div class="p-6 bg-white rounded-lg shadow">
                <h2 class="text-2xl font-semibold text-gray-700">Assessment</h2>
                    <p class="mt-4 text-4xl font-bold text-red-500" id="total-assessments"><?php echo $total_assessments;?></p>
                    <p class="mt-2 text-sm text-gray-500">currently in progress</p>
            </div>
        </div>
    </section>

    <!-- ANNOUNCEMENTS SECTION -->
    <h4 class="p-2 text-xl font-semibold text-white rounded-lg blue-dark">ANNOUNCEMENTS</h4>
        <section class="p-4 mb-10 bg-white border-2 rounded-lg shadow-lg">
            <div class="grid grid-cols-1 gap-4 my-3 sm:grid-cols-2">
                <?php if (empty($announcements)): ?>
                    <p class="text-gray-500 col-span-full">No announcements available.</p>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="p-6 bg-white rounded-lg shadow-lg">
                            <h3 class="mb-2 text-2xl font-semibold text-blue-800">
                                <?= htmlspecialchars($announcement['title']); ?>
                            </h3>
                            <p class="text-gray-700">
                                <?= nl2br(htmlspecialchars($announcement['content'])); ?>
                            </p>
                            <p class="mt-2 text-sm text-gray-500">
                                Posted on: <?= date('F j, Y, g:i a', strtotime($announcement['published_at'])); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

<!-- FOOTER -->
<footer class="w-full mt-auto bg-gray-100">
        <div class="w-full max-w-screen-xl p-4 py-6 mx-auto text-gray-800 lg:py-8">
            <div class="md:flex md:justify-between">
                <div class="mb-6 md:mb-0">
                    <a href="#" class="flex items-center">
                        <img src="/src/images/UMAK-CGCS-logo.png" class="h-8 me-3" alt="GuidanceHub Logo" />
                        <span class="self-center text-2xl font-semibold whitespace-nowrap">GuidanceHub</span>
                    </a>
                </div>
                <div class="grid grid-cols-2 gap-8 sm:gap-6 sm:grid-cols-3">
                    <div>
                        <h2 class="mb-6 text-sm font-semibold text-black uppercase">Resources</h2>
                        <ul class="font-medium text-gray-500">
                            <li class="mb-4"><a href="#" class="hover:underline">GuidanceHub</a></li>
                            <li><a href="https://tailwindcss.com/" class="hover:underline">Tailwind CSS</a></li>
                        </ul>
                    </div>
                    <div>
                        <h2 class="mb-6 text-sm font-semibold text-black uppercase">Follow us</h2>
                        <ul class="font-medium text-gray-500">
                            <li class="mb-4"><a href="#" class="hover:underline">Github</a></li>
                            <li><a href="#" class="hover:underline">Discord</a></li>
                        </ul>
                    </div>
                    <div>
                        <h2 class="mb-6 text-sm font-semibold text-black uppercase">Legal</h2>
                        <ul class="font-medium text-gray-500">
                            <li class="mb-4"><a href="#" class="hover:underline">Privacy Policy</a></li>
                            <li><a href="#" class="hover:underline">Terms & Conditions</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <hr class="my-6 border-gray-200 sm:mx-auto dark:border-gray-300 lg:my-8" />
            <div class="sm:flex sm:items-center sm:justify-between">
                <span class="text-sm text-gray-500 sm:text-center">© 2023 Flowbite™. All Rights Reserved.</span>
                <div class="flex mt-4 space-x-5 sm:justify-center sm:mt-0">
                    <!-- Icons here -->
                </div>
            </div>
        </div>
    </footer>
</div>

<script>
//Toggle for Message
const messageButton = document.getElementById('messageButton');
    const chatModal = document.getElementById('chatModal');
    const messageBadge = document.getElementById('messageBadge');
    const chatContent = document.getElementById('chatContent');
    const chatInput = document.getElementById('chatInput');

    // Show chat modal when message icon is clicked
    messageButton.addEventListener('click', () => {
        chatModal.classList.toggle('hidden');
    });

    // Close the chat modal
    function closeChatModal() {
        chatModal.classList.add('hidden');
    }

    // Send a new message
    function sendMessage() {
        const messageText = chatInput.value.trim();
        if (messageText) {
            const newMessage = document.createElement('div');
            newMessage.classList.add('mb-2');
            newMessage.innerHTML = `<p class="p-2 text-blue-800 bg-blue-100 rounded">${messageText}</p>`;
            chatContent.appendChild(newMessage);
            chatInput.value = ''; // Clear input after sending
            chatContent.scrollTop = chatContent.scrollHeight; // Scroll to the latest message
        }
    }

    // Simulate unread messages (this would be dynamic in a real app)
    function simulateUnreadMessages() {
        const unreadMessages = 3; // Example count of unread messages
        if (unreadMessages > 0) {
            messageBadge.textContent = unreadMessages;
            messageBadge.classList.remove('hidden');
        } else {
            messageBadge.classList.add('hidden');
        }
    }

    // Initialize unread message count
    simulateUnreadMessages();

//Toggle for Notification
const notificationButton = document.getElementById('notificationButton');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationList = document.getElementById('notificationList');
    const markReadButton = document.getElementById('markReadButton');

    // Sample notifications array
    let notifications = [
        "Your appointment has been confirmed.",
        "New message from the guidance office.",
        "Reminder: Your appointment is tomorrow at 10:00 AM."
    ];

    // Function to display notifications
    function updateNotifications() {
        if (notifications.length > 0) {
            notificationBadge.textContent = notifications.length;
            notificationBadge.classList.remove('hidden');
            markReadButton.classList.remove('hidden');

            // Update the dropdown list
            notificationList.innerHTML = notifications.map(
                (notif) => `<li class="p-2 bg-gray-100 rounded hover:bg-gray-200">${notif}</li>`
            ).join('');
        } else {
            notificationBadge.classList.add('hidden');
            markReadButton.classList.add('hidden');
            notificationList.innerHTML = `<li class="text-gray-500">No new notifications</li>`;
        }
    }

    // Show or hide the dropdown
    notificationButton.addEventListener('click', () => {
        notificationDropdown.classList.toggle('hidden');
    });

    // Mark all notifications as read
    markReadButton.addEventListener('click', () => {
        notifications = []; // Clear notifications array
        updateNotifications(); // Update the UI
    });

    // Initialize notifications on page load
    updateNotifications();

// JavaScript to handle search box toggling and icon change
    document.addEventListener('DOMContentLoaded', function () {
        const searchToggle = document.getElementById('search-toggle');
        const searchBox = document.getElementById('search-box');
        const searchIcon = document.getElementById('search-icon');
        const searchExit = document.getElementById('search-exit');

        // Toggle the search box and icon when search icon is clicked
        searchToggle.addEventListener('click', function () {
            // Toggle search box visibility
            if (searchBox.classList.contains('opacity-0')) {
                searchBox.classList.remove('opacity-0', 'max-h-0');
                searchBox.classList.add('opacity-100', 'max-h-screen');
                searchIcon.classList.remove('fa-magnifying-glass');
                searchIcon.classList.add('fa-circle-xmark');  // Change to exit icon
            } else {
                searchBox.classList.add('opacity-0', 'max-h-0');
                searchBox.classList.remove('opacity-100', 'max-h-screen');
                searchIcon.classList.remove('fa-circle-xmark'); // Revert to search icon
                searchIcon.classList.add('fa-magnifying-glass');
            }
        });

        // Hide search box when clicking the exit icon
        searchExit.addEventListener('click', function () {
            searchBox.classList.add('opacity-0', 'max-h-0');
            searchBox.classList.remove('opacity-100', 'max-h-screen');
            searchIcon.classList.remove('fa-circle-xmark');
            searchIcon.classList.add('fa-magnifying-glass'); // Revert to search icon
        });
    });


</script>
<script src="../path/to/flowbite/dist/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>