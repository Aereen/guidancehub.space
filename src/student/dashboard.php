<?php
// Start session at the very beginning
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

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header("Location: /src/ControlledData/login.php"); // Redirect to login
    exit;
}

$user_email = $_SESSION['email']; // Store email in a variable

// Fetch announcements
$announcements = [];
$sql = "SELECT * FROM announcement ORDER BY published_at DESC";
$result = $con->query($sql);
if ($result) {
    $announcements = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Error fetching announcements: " . $con->error);
}

// Fetch appointments
$appointments = [];
if ($stmt = $con->prepare("SELECT * FROM appointments WHERE student_email = ? AND (first_date >= NOW() OR second_date >= NOW())")) {
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    die("Error preparing appointments query: " . $con->error);
}

// Fetch assessments
$assessments = [];

if (!empty($student_email)) {
    $stmt = mysqli_prepare($con, "SELECT schedule_date, schedule_time, status FROM assessments WHERE student_email = ?");
    mysqli_stmt_bind_param($stmt, "s", $student_email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $assessments[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

// Fetch Individual Inventory 
$individual_inventory = null;

if ($user_email) {
    $sql = "SELECT student_name, student_email, college_dept, year_level FROM individual_inventory WHERE student_email = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $user_email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $individual_inventory = mysqli_fetch_assoc($result);
    }

    mysqli_stmt_close($stmt);
}

// Check if student has answered the form
$hasAnswered = false;
if ($stmt = $con->prepare("SELECT id FROM individual_inventory WHERE student_email = ?")) {
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    // Set hasAnswered to true if a row was found
    $hasAnswered = $row !== null;
} else {
    die("Error preparing form response query: " . $con->error);
}


// Function to check if a student has been referred
function isStudentReferred($con, $student_email) {
    $query = "SELECT COUNT(*) AS total FROM referrals WHERE student_name = ?";
    $stmt = $con->prepare($query);

    if (!$stmt) {
        die("Prepare failed: " . $con->error);
    }

    $stmt->bind_param("s", $student_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("Execute failed: " . $stmt->error);
    }

    $row = $result->fetch_assoc();
    return $row['total'] > 0;
}

$student_email = $_SESSION['email'];

$isReferred = isStudentReferred($con, $student_email);

//calendar
// Set timezone
date_default_timezone_set('Asia/Manila');

// Get current month and year
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Get first day of the month and total days in the month
$firstDayOfMonth = date('w', strtotime("$year-$month-01"));
$totalDays = date('t', strtotime("$year-$month-01"));

// Days of the week
$daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// Generate calendar
$calendar = [];
$row = array_fill(0, 7, null);
$dayCounter = 1;

for ($i = 0; $i < 42; $i++) {
    if ($i >= $firstDayOfMonth && $dayCounter <= $totalDays) {
        $row[$i % 7] = $dayCounter++;
    }

    if ($i % 7 === 6) {
        $calendar[] = $row;
        $row = array_fill(0, 7, null);
    }
}

// When logout is requested
if (isset($_GET['logout'])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: /index.php"); // Redirect after logout
    exit;
}

// Close connection at the end
$con->close();
?>

<!doctype html>
<html>
<head>
<title>GuidanceHub | Dashboard</title>
    <link rel="icon" type="images/x-icon" href="/src/images/UMAK-CGCS-logo.png" />
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            color: #618dc2; }

    </style>
</head>
<body class="bg-gray-100">

<!--TOP NAVIGATION BAR-->
<header class="fixed top-0 left-0 z-50 w-full shadow-md blue-dark marcellus-regular">
    <div class="flex px-3 py-4 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between w-full mx-auto max-w-7xl">

            <!-- LOGO -->
            <div class="flex items-center mx-5">
                <img src="/src/images/UMAK-CGCS-logo.png" alt="CGCS Logo" class="w-10 h-auto md:w-14">
                <span class="ml-4 font-semibold tracking-widest text-white md:text-2xl">GuidanceHub</span>
            </div>

            <!-- Hamburger Icon (Mobile) -->
            <button id="menu-toggle" class="text-2xl text-white md:hidden focus:outline-none">
                <i class="fa-solid fa-bars"></i>
            </button>

            <!-- Navigation Links (Desktop) -->
            <nav id="nav-menu" class="items-center hidden space-x-6 md:flex">
                <a href="dashboard.php" class="text-white blue-2">Dashboard</a>
                <a href="library.php" class="text-white blue-2">Library</a>

                <!-- Messages Icon -->
                <div class="relative">
                    <button id="messageButton" class="text-white blue-2 focus:outline-none">
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
            </nav>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="flex-col items-start hidden w-full px-6 py-4 space-y-4 blue-dark md:hidden">
        <a href="dashboard.php" class="block px-3 py-2 font-medium text-white transition rounded hover:bg-blue-900">Dashboard</a>
        <a href="library.php" class="block px-3 py-2 font-medium text-white transition rounded hover:bg-blue-900">Library</a>
        <a href="?logout=true" class="block text-white font-semibold text-center py-2 px-4 rounded bg-[#618dc2] hover:bg-blue-600 transition">
            <i class="mr-2 fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</header>

<h2 class="p-3 text-4xl font-bold tracking-tight mt-28 ml-7"><?php echo "Welcome, " . $_SESSION['name'] . "!<br>" ?></h2>

<!--CONTENT-->
<main class="w-full p-4">
<div class="grid grid-cols-1 gap-4 p-2 mt-2 mb-5 lg:grid-cols-3">
    <div class="space-y-4 lg:col-span-2">

        <section class="p-5 bg-white border-2 rounded-lg shadow-lg">
            <h2 class="m-5 text-3xl font-bold">REMINDERS</h2>

            <!-- Reminder Message for Incomplete Individual Form -->
                <?php if (!$hasAnswered): ?>
                    <div class="p-4 bg-yellow-100 border-l-4 border-yellow-500 rounded">
                        <p class="font-semibold text-yellow-700">⚠️ Reminder: You have not yet completed the form.</p>
                        <a href="/src/ControlledData/information.php" class="inline-block px-4 py-2 mt-3 text-white bg-blue-500 rounded hover:bg-blue-600">
                            Complete Form
                        </a>
                    </div>
                <?php endif; ?>

            <!-- Reminder Message for Refferal Counseling -->
                <?php if (!$isReferred): ?>
                    <div class="p-4 bg-yellow-100 border-l-4 border-yellow-500 rounded">
                        <p class="font-semibold text-yellow-700">⚠️ Reminder: You have been referred to the counseling office. 
                                                                    <br> Redirect to Counseling Appointment to shcedule your preferred counseling time and date.</p>
                        <a href="/src/ControlledData/appointment.php" class="inline-block px-4 py-2 mt-3 text-white bg-blue-500 rounded hover:bg-blue-600">
                            Schedule Counseling
                        </a>
                    </div>
                <?php endif; ?>

            <div class="grid grid-cols-1 gap-4 mt-4 sm:grid-cols-2">

                <!-- UPCOMING SESSIONS -->
                <div class="p-6 bg-white rounded-lg shadow-md">
                    <h2 class="mb-3 text-xl font-bold text-gray-700 underline">Upcoming Appointments</h2>
                    <ul class="space-y-3">
                        <?php if (!empty($appointments)): ?>
                            <?php foreach ($appointments as $appointment): ?>
                                <li class="flex flex-col space-y-1 text-gray-800">
                                    <span class="font-medium">First Date & Time:</span>
                                    <span><?= htmlspecialchars($appointment['first_date']) ?> | <?= htmlspecialchars($appointment['first_time']) ?></span>
                                    <span class="font-medium">Second Date & Time:</span>
                                    <span><?= htmlspecialchars($appointment['second_date']) ?> | <?= htmlspecialchars($appointment['second_time']) ?></span>
                                    <span class="font-medium">Status:</span>
                                    <span><?= htmlspecialchars($appointment['status']) ?: 'Pending'; ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-gray-600">No Appointments...</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- PENDING ASSESSMENTS -->
                <div class="p-6 bg-white rounded-lg shadow-md">
                    <h2 class="mb-3 text-xl font-bold text-gray-700 underline">Scheduled Assessments</h2>
                    <ul class="space-y-3">
                        <?php if (!empty($assessments)): ?>
                            <?php foreach ($assessments as $assessment): ?>
                                <li class="flex flex-col space-y-1 text-gray-800">
                                    <span class="font-medium">Assessment Date:</span>
                                    <span><?= htmlspecialchars($assessment['schedule_date']) ?> | <?= htmlspecialchars($assessment['schedule_time']) ?></span>
                                    <span class="font-medium">Status:</span>
                                    <span><?= htmlspecialchars($assessment['status']) ?: 'Pending'; ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-gray-600">No Assessments...</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Announcements Section -->
        <section class="p-4 bg-white border-2 rounded-lg shadow-lg">
            <h4 class="p-2 text-xl font-semibold text-white rounded-lg blue-dark">ANNOUNCEMENTS</h4>
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
    </div>

    <!-- Right Column: Profile & Calendar -->
    <div class="space-y-4">
        <!-- PROFILE CARD -->
        <div class="w-full max-w-xl p-6 mx-auto bg-white border border-gray-200 shadow-md rounded-xl">
            <h4 class="mb-4 text-2xl font-bold text-center text-gray-800">Profile</h4>
            
            <div class="flex flex-col items-start space-y-4">
                <?php if (!empty($individual_inventory) && is_array($individual_inventory)): ?>
                    <div class="w-full">
                        <div class="flex justify-between py-2 border-b">
                            <span class="font-medium text-gray-700">Name:</span>
                            <span class="text-gray-900"><?= htmlspecialchars($individual_inventory['student_name'] ?? '') ?></span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="font-medium text-gray-700">UMak Email:</span>
                            <span class="text-gray-900"><?= htmlspecialchars($individual_inventory['student_email'] ?? '') ?></span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="font-medium text-gray-700">College/Institute:</span>
                            <span class="text-gray-900"><?= htmlspecialchars($individual_inventory['college_dept'] ?? '') ?></span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="font-medium text-gray-700">Year Level:</span>
                            <span class="text-gray-900"><?= htmlspecialchars($individual_inventory['year_level'] ?? '') ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="w-full p-4 bg-red-100 border-l-4 border-red-500 rounded">
                        <p class="text-sm font-semibold text-red-700">
                            Error: Unable to load profile information. Please ensure you have filled out the Individual Inventory Form.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>


        <!-- CALENDAR -->
        <div class="p-3 bg-white border-2 rounded-lg shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <button onclick="prevMonth()" class="p-2 text-white rounded blue-dark">←</button>
                <h2 id="currentMonth" class="text-2xl font-bold text-gray-800" data-date="<?= "$year-$month-01"; ?>">
                    <?= date('F Y', strtotime("$year-$month-01")); ?>
                </h2>
                <button onclick="nextMonth()" class="p-2 text-white rounded blue-dark">→</button>
            </div>
    
            <!-- Days of the Week -->
            <div class="grid grid-cols-7 gap-2 font-semibold text-center text-gray-600 blue-dark">
                <?php foreach ($daysOfWeek as $day): ?>
                    <div class="p-1 text-white rounded-lg"><?= htmlspecialchars($day); ?></div>
                <?php endforeach; ?>
            </div>
    
            <!-- Calendar Days -->
            <div class="grid grid-cols-7 gap-2 mt-2 text-center" id="calendarDays">
                <?php foreach ($calendar as $week): ?>
                    <?php foreach ($week as $day): ?>
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <?= $day ?: ''; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    
    </div>
</div>
</main>

<!-- COUNSELING PROCESS -->
<div class="flex flex-col items-center w-full">
    <h4 class="w-5/6 p-2 text-xl font-semibold text-center text-white rounded-lg blue-dark">
        PROCESS
    </h4>
    <section class="grid w-5/6 grid-cols-1 gap-4 p-5 my-5 bg-white border-2 rounded-lg md:grid-cols-2 lg:grid-cols-3">
        <!-- Card 1 -->
        <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow min-h-[180px]">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-black">Needs Assessment</h5>
            <p class="text-gray-700">Identify students' needs and concerns through surveys or questionnaires...</p>
        </div>

        <!-- Card 2 -->
        <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow min-h-[180px]">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-black">Program Planning</h5>
            <p class="text-gray-700">Plan the delivery of services, such as individual counseling...</p>
        </div>

        <!-- Card 3 -->
        <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow min-h-[180px]">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-black">Counseling and Intervention</h5>
            <p class="text-gray-700">One-on-one support through individual counseling...</p>
        </div>

        <!-- Card 4 -->
        <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow min-h-[180px]">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-black">Follow-up Consultation</h5>
            <p class="text-gray-700">Monitor student progress and provide ongoing support...</p>
        </div>

        <!-- Card 5 -->
        <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow min-h-[180px]">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-black">Evaluation & Program Improvement</h5>
            <p class="text-gray-700">Conduct regular evaluations to assess effectiveness...</p>
        </div>
    </section>
</div>



<!--SCHEDULING CALL TO ACTION-->
<section class="flex items-center justify-center w-full bg-yellow-300">
    <div class="p-8 text-center">
        <h2 class="mb-4 text-3xl font-semibold text-gray-800">Schedule Your Counseling Appointment</h2>
        <p class="mb-6 text-lg text-gray-600">
            Taking the first step toward mental well-being is easy. Book an appointment with our counselors today.
        </p>

        <!-- Call to Action Link -->
        <a href="/src/ControlledData/appointment.php" class="inline-block px-6 py-3 text-xl text-white transition duration-300 bg-blue-600 rounded-lg hover:bg-blue-700">
            Book Your Appointment
        </a>

        <!-- Optional: Contact Details -->
        <div class="mt-6 text-sm text-gray-500">
            <p>If you need assistance, call us at <strong>(123) 456-7890</strong> or email <strong>support@counseling.com</strong></p>
        </div>
    </div>
</section>

<!--FOOTER-->
<footer class="w-full blue-dark">
    <div class="w-full max-w-screen-xl p-4 py-6 mx-auto text-white lg:py-8">
        <div class="md:flex md:justify-between">
            <div class="mb-6 md:mb-0">
                <a href="https://flowbite.com/" class="flex items-center">
                    <img src="/src/images/UMAK-CGCS-logo.png" class="h-8 me-3" alt="GuidanceHub Logo" />
                    <span class="font-bold tracking-wide text-white md:text-2xl">GuidanceHub</span>
                </a>
            </div>
            <div class="grid grid-cols-2 gap-8 sm:gap-6 sm:grid-cols-3">
                <div>
                    <h2 class="mb-6 text-sm font-semibold uppercase">Resources</h2>
                    <ul class="font-medium">
                        <li class="mb-4">
                            <a href="https://flowbite.com/" class="hover:underline">GuidanceHub</a>
                        </li>
                        <li>
                            <a href="https://tailwindcss.com/" class="hover:underline">Tailwind CSS</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h2 class="mb-6 text-sm font-semibold uppercase">Follow us</h2>
                    <ul class="font-medium">
                        <li class="mb-4">
                            <a href="https://github.com/themesberg/flowbite" class="hover:underline">Github</a>
                        </li>
                        <li>
                            <a href="https://discord.gg/4eeurUVvTy" class="hover:underline">Discord</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h2 class="mb-6 text-sm font-semibold uppercase">Legal</h2>
                    <ul class="font-medium">
                        <li class="mb-4">
                            <a href="#" class="hover:underline">Privacy Policy</a>
                        </li>
                        <li>
                            <a href="#" class="hover:underline">Terms &amp; Conditions</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="sm:flex sm:items-center sm:justify-between">
            <span class="text-sm sm:text-center">© 2025 Group 8 | IV-AINS. All Rights Reserved.
            </span>
        </div>
    </div>
</footer>

<script>
//Toggling Menu
document.getElementById('menu-toggle').addEventListener('click', function () {
        document.getElementById('mobile-menu').classList.toggle('hidden');
    });

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
</script>
<script src="../path/to/flowbite/dist/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>