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

// Close connection at the end
$con->close();
?>


<!doctype html>
<html>
<head>
<title> GuidanceHub </title>
    <link rel="icon" type="images/x-icon" href="/src/images/UMAK-CGCS-logo.png" />
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css"  rel="stylesheet" />
        <script src="https://kit.fontawesome.com/95c10202b4.js" crossorigin="anonymous"></script>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="./output.css" rel="stylesheet">   

</head>
<body class="bg-gray-100">

<!-- TOP NAVIGATION BAR -->
<nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200">
    <div class="flex px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between w-full max-w-7xl">
            <div class="flex items-center justify-start">
                <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                    </svg>
                </button>
                <a href="" class="flex ms-2 md:me-24">
                    <img src="/src/images/UMAK-CGCS-logo.png" class="h-8 me-3" alt="GuidanceHub Logo" />
                    <span class="self-center text-xl font-semibold text-black sm:text-2xl whitespace-nowrap">GuidanceHub</span>
                </a>
            </div>
            <div class="flex items-center justify-end gap-7 text-gray">
                <!--Message Icon-->
                    <div class="relative">
                        <button id="messageButton" class="text-gray-600 hover:text-gray-900 focus:outline-none">
                            <i class="text-2xl fa-solid fa-message"></i>
                            <!-- Unread Message Badge -->
                            <span id="messageBadge" class="absolute top-0 right-0 inline-flex items-center justify-center hidden w-4 h-4 text-xs font-bold text-white bg-red-500 rounded-full">
                                3
                            </span>
                        </button>
                        <!-- Message Chat Modal -->
                        <div id="chatModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
                            <div class="w-full max-w-lg p-6 bg-white rounded-lg shadow-md">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-semibold">Chat with Support</h3>
                                    <button onclick="closeChatModal()" class="text-gray-500 hover:text-gray-800">✖</button>
                                </div>
                                <div id="chatContent" class="h-64 mb-4 overflow-y-auto text-sm text-gray-700">
                                    <div class="mb-2">
                                        <p class="p-2 bg-gray-100 rounded">Hello! How can I assist you today?</p>
                                    </div>
                                    <div class="mb-2">
                                        <p class="p-2 text-blue-800 bg-blue-100 rounded">I need help with my appointment.</p>
                                    </div>
                                </div>
                                <div class="flex">
                                    <input id="chatInput" type="text" class="w-full p-2 border border-gray-300 rounded-l-md" placeholder="Type your message...">
                                    <button onclick="sendMessage()" class="px-4 py-2 text-white bg-blue-500 rounded-r-md hover:bg-blue-700">Send</button>
                                </div>
                            </div>
                        </div>
                    </div>

                <!--Notification Bell Icon-->
                    <div class="relative">
                        <button id="notificationButton" class="text-gray-600 hover:text-gray-900 focus:outline-none">
                            <i class="text-2xl fa-solid fa-bell"></i>
                            <!-- Notification Badge -->
                            <span id="notificationBadge" class="absolute top-0 right-0 inline-flex items-center justify-center hidden w-4 h-4 text-xs font-bold text-white bg-red-500 rounded-full">
                                0
                            </span>
                        </button>
                        <!-- Notification Dropdown -->
                        <div id="notificationDropdown" class="absolute right-0 z-50 hidden w-64 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg">
                            <div class="p-4 text-sm text-gray-700">
                                <h4 class="text-lg font-bold">Notifications</h4>
                                <ul id="notificationList" class="mt-2 space-y-2">
                                    <li class="text-gray-500">No new notifications</li>
                                </ul>
                                <!-- Mark as Read Button -->
                                <button id="markReadButton" class="hidden w-full px-4 py-2 mt-4 text-sm font-medium text-white bg-blue-500 rounded hover:bg-blue-700">
                                    Mark All as Read
                                </button>
                            </div>
                        </div>
                    </div>

                <!-- Search Icon -->
                <div class="relative">
                    <button
                        id="search-toggle"
                        class="text-xl text-gray-700 hover:text-blue-600 focus:outline-none">
                        <i id="search-icon" class="fa-solid fa-magnifying-glass"></i>
                    </button>

                    <!-- Search Box (Hidden Initially) -->
                    <div id="search-box" class="absolute right-0 p-4 mt-2 overflow-hidden transition-all duration-300 ease-in-out bg-white border border-gray-300 rounded-lg shadow-lg opacity-0 w-80 max-h-0">
                        <form action="" method="GET" class="w-full max-w-md mx-auto">
                            <label for="default-search" class="mb-2 text-sm font-medium sr-only">Search</label>
                            <div class="relative">
                                <input type="search" id="default-search" name="query"
                                    class="block w-full p-4 text-sm text-gray-900"
                                    placeholder="Search" />
                                <button type="submit"
                                    class="absolute px-4 py-2 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 right-2 bottom-2">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- SIDE NAVIGATION MENU -->
<aside id="logo-sidebar" class="fixed z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r dark:border-gray-300 sm:translate-x-0" aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-white border-gray-300">
        <ul class="m-3 space-y-2 font-medium">
            <li>
                <a href="dashboard.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                    <i class="fa-solid fa-house"></i>
                </svg>
                <span class="ms-3">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="report.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
                    <i class="fa-solid fa-calendar-check"></i>
                </svg>
                <span class="flex-1 ms-3 whitespace-nowrap">Report</span>
                </a>
            </li>
            <li>
                <a href="analytics.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
                    <i class="fa-solid fa-chart-pie"></i>
                </svg>
                <span class="flex-1 ms-3 whitespace-nowrap">Analytic</span>
                </a>
            </li>
            <li>
                <a href="resources.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
                    <i class="fa-solid fa-chart-pie"></i>
                </svg>
                <span class="flex-1 ms-3 whitespace-nowrap">Resources</span>
                </a>
            </li>
            <li>
                <a href="audit.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
                    <i class="fa-solid fa-chart-pie"></i>
                </svg>
                <span class="flex-1 ms-3 whitespace-nowrap">Audit Log</span>
                </a>
            </li>
            <li>
                <a href="?logout=true" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                    <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 16">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/> <i class="fa-solid fa-right-from-bracket"></i>
                    </svg>
                    <span class="flex-1 ms-3 whitespace-nowrap">Log Out</span>
                </a>
            </li>
        </ul>
    </div>
</aside>

<!--CONTENT HERE-->
<section class="p-4 mt-12 sm:ml-64">
<h2 class="p-3 text-4xl font-bold tracking-tight"><?php echo "Welcome, " . $_SESSION['name'] . "!<br>" ?></h2>
    <div class="px-4 py-3 mx-auto max-w-7xl">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Total Appointments -->
            <div class="p-6 bg-white rounded-lg shadow">
                <h2 class="text-2xl font-semibold text-gray-700">Appointment</h2>
                <p class="mt-4 text-4xl font-bold text-green-500" id="total-appointments"><?php echo $total_appointments; ?></p>
                <p class="mt-2 text-sm text-gray-500">total sessions</p>
            </div>
            <!-- Total Referrals -->
            <div class="p-6 bg-white rounded-lg shadow">
                <h2 class="text-2xl font-semibold text-gray-700">Referral</h2>
                <p class="mt-4 text-4xl font-bold text-blue-500" id="total-referrals"><?php echo $total_referrals; ?></p>
                <p class="mt-2 text-sm text-gray-500">number of students</p>
            </div>
            <!-- Total Assessments -->
            <div class="p-6 bg-white rounded-lg shadow">
                <h2 class="text-2xl font-semibold text-gray-700">Assessment</h2>
                <p class="mt-4 text-4xl font-bold text-red-500" id="total-assessments"><?php echo $total_assessments; ?></p>
                <p class="mt-2 text-sm text-gray-500">currently in progress</p>
            </div>
        </div>
        
        <!-- Announcements Section -->
        <section class="p-4 bg-white border-2 rounded-lg shadow-lg">
            <h4 class="p-2 text-xl font-semibold text-white bg-teal-500 rounded-lg">ANNOUNCEMENTS</h4>
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
</section>

<!--FOOTER-->
<footer class="overflow-auto bg-gray-100 sm:ml-64 w-75">
    <div class="w-full max-w-screen-xl p-4 py-6 mx-auto lg:py-8 dark:text-gray-800">
        <div class="md:flex md:justify-between">
            <div class="mb-6 md:mb-0">
                <a href="https://flowbite.com/" class="flex items-center">
                    <img src="/src/images/UMAK-CGCS-logo.png" class="h-8 me-3" alt="GuidanceHub Logo" />
                    <span class="self-center text-2xl font-semibold whitespace-nowrap">GuidanceHub<span>
                </a>
            </div>
            <div class="grid grid-cols-2 gap-8 sm:gap-6 sm:grid-cols-3">
                <div>
                    <h2 class="mb-6 text-sm font-semibold text-black uppercase">Resources</h2>
                    <ul class="font-medium text-gray-500 dark:text-gray-400">
                        <li class="mb-4">
                            <a href="https://flowbite.com/" class="hover:underline">GuidanceHub</a>
                        </li>
                        <li>
                            <a href="https://tailwindcss.com/" class="hover:underline">Tailwind CSS</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase">Follow us</h2>
                    <ul class="font-medium text-gray-500 dark:text-gray-400">
                        <li class="mb-4">
                            <a href="https://github.com/themesberg/flowbite" class="hover:underline">Github</a>
                        </li>
                        <li>
                            <a href="https://discord.gg/4eeurUVvTy" class="hover:underline">Discord</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase">Legal</h2>
                    <ul class="font-medium text-gray-500 dark:text-gray-400">
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
        <hr class="my-6 border-gray-200 sm:mx-auto dark:border-gray-300 lg:my-8" />
        <div class="sm:flex sm:items-center sm:justify-between">
            <span class="text-sm text-gray-500 sm:text-center dark:text-gray-400">© 2023 <a href="https://flowbite.com/" class="hover:underline">Flowbite™</a>. All Rights Reserved.
            </span>
            <div class="flex mt-4 sm:justify-center sm:mt-0">
                <a href="#" class="text-gray-500 hover:text-gray-900 dark:hover:text-white">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 8 19">
                            <path fill-rule="evenodd" d="M6.135 3H8V0H6.135a4.147 4.147 0 0 0-4.142 4.142V6H0v3h2v9.938h3V9h2.021l.592-3H5V3.591A.6.6 0 0 1 5.592 3h.543Z" clip-rule="evenodd"/>
                        </svg>
                    <span class="sr-only">Facebook page</span>
                </a>
                <a href="#" class="text-gray-500 hover:text-gray-900 dark:hover:text-white ms-5">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 21 16">
                            <path d="M16.942 1.556a16.3 16.3 0 0 0-4.126-1.3 12.04 12.04 0 0 0-.529 1.1 15.175 15.175 0 0 0-4.573 0 11.585 11.585 0 0 0-.535-1.1 16.274 16.274 0 0 0-4.129 1.3A17.392 17.392 0 0 0 .182 13.218a15.785 15.785 0 0 0 4.963 2.521c.41-.564.773-1.16 1.084-1.785a10.63 10.63 0 0 1-1.706-.83c.143-.106.283-.217.418-.33a11.664 11.664 0 0 0 10.118 0c.137.113.277.224.418.33-.544.328-1.116.606-1.71.832a12.52 12.52 0 0 0 1.084 1.785 16.46 16.46 0 0 0 5.064-2.595 17.286 17.286 0 0 0-2.973-11.59ZM6.678 10.813a1.941 1.941 0 0 1-1.8-2.045 1.93 1.93 0 0 1 1.8-2.047 1.919 1.919 0 0 1 1.8 2.047 1.93 1.93 0 0 1-1.8 2.045Zm6.644 0a1.94 1.94 0 0 1-1.8-2.045 1.93 1.93 0 0 1 1.8-2.047 1.918 1.918 0 0 1 1.8 2.047 1.93 1.93 0 0 1-1.8 2.045Z"/>
                        </svg>
                    <span class="sr-only">Discord community</span>
                </a>
                <a href="#" class="text-gray-500 hover:text-gray-900 dark:hover:text-white ms-5">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 17">
                        <path fill-rule="evenodd" d="M20 1.892a8.178 8.178 0 0 1-2.355.635 4.074 4.074 0 0 0 1.8-2.235 8.344 8.344 0 0 1-2.605.98A4.13 4.13 0 0 0 13.85 0a4.068 4.068 0 0 0-4.1 4.038 4 4 0 0 0 .105.919A11.705 11.705 0 0 1 1.4.734a4.006 4.006 0 0 0 1.268 5.392 4.165 4.165 0 0 1-1.859-.5v.05A4.057 4.057 0 0 0 4.1 9.635a4.19 4.19 0 0 1-1.856.07 4.108 4.108 0 0 0 3.831 2.807A8.36 8.36 0 0 1 0 14.184 11.732 11.732 0 0 0 6.291 16 11.502 11.502 0 0 0 17.964 4.5c0-.177 0-.35-.012-.523A8.143 8.143 0 0 0 20 1.892Z" clip-rule="evenodd"/>
                    </svg>
                    <span class="sr-only">Twitter page</span>
                </a>
                <a href="#" class="text-gray-500 hover:text-gray-900 dark:hover:text-white ms-5">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 .333A9.911 9.911 0 0 0 6.866 19.65c.5.092.678-.215.678-.477 0-.237-.01-1.017-.014-1.845-2.757.6-3.338-1.169-3.338-1.169a2.627 2.627 0 0 0-1.1-1.451c-.9-.615.07-.6.07-.6a2.084 2.084 0 0 1 1.518 1.021 2.11 2.11 0 0 0 2.884.823c.044-.503.268-.973.63-1.325-2.2-.25-4.516-1.1-4.516-4.9A3.832 3.832 0 0 1 4.7 7.068a3.56 3.56 0 0 1 .095-2.623s.832-.266 2.726 1.016a9.409 9.409 0 0 1 4.962 0c1.89-1.282 2.717-1.016 2.717-1.016.366.83.402 1.768.1 2.623a3.827 3.827 0 0 1 1.02 2.659c0 3.807-2.319 4.644-4.525 4.889a2.366 2.366 0 0 1 .673 1.834c0 1.326-.012 2.394-.012 2.72 0 .263.18.572.681.475A9.911 9.911 0 0 0 10 .333Z" clip-rule="evenodd"/>
                    </svg>
                    <span class="sr-only">GitHub account</span>
                </a>
                <a href="#" class="text-gray-500 hover:text-gray-900 dark:hover:text-white ms-5">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 0a10 10 0 1 0 10 10A10.009 10.009 0 0 0 10 0Zm6.613 4.614a8.523 8.523 0 0 1 1.93 5.32 20.094 20.094 0 0 0-5.949-.274c-.059-.149-.122-.292-.184-.441a23.879 23.879 0 0 0-.566-1.239 11.41 11.41 0 0 0 4.769-3.366ZM8 1.707a8.821 8.821 0 0 1 2-.238 8.5 8.5 0 0 1 5.664 2.152 9.608 9.608 0 0 1-4.476 3.087A45.758 45.758 0 0 0 8 1.707ZM1.642 8.262a8.57 8.57 0 0 1 4.73-5.981A53.998 53.998 0 0 1 9.54 7.222a32.078 32.078 0 0 1-7.9 1.04h.002Zm2.01 7.46a8.51 8.51 0 0 1-2.2-5.707v-.262a31.64 31.64 0 0 0 8.777-1.219c.243.477.477.964.692 1.449-.114.032-.227.067-.336.1a13.569 13.569 0 0 0-6.942 5.636l.009.003ZM10 18.556a8.508 8.508 0 0 1-5.243-1.8 11.717 11.717 0 0 1 6.7-5.332.509.509 0 0 1 .055-.02 35.65 35.65 0 0 1 1.819 6.476 8.476 8.476 0 0 1-3.331.676Zm4.772-1.462A37.232 37.232 0 0 0 13.113 11a12.513 12.513 0 0 1 5.321.364 8.56 8.56 0 0 1-3.66 5.73h-.002Z" clip-rule="evenodd"/>
                    </svg>
                    <span class="sr-only">Dribbble account</span>
                </a>
            </div>
        </div>
    </div>
</footer>

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


// Fake Data
        const stats = {
            studentsServed: Math.floor(Math.random() * (200 - 100 + 1)) + 100,
            sessionsConducted: Math.floor(Math.random() * (200 - 50 + 1)) + 50,
            activeCases: Math.floor(Math.random() * (50 - 10 + 1)) + 10,
        };

        const reports = [
            { name: "John Doe", issue: "Stress", status: "Resolved" },
            { name: "Jane Smith", issue: "Anxiety", status: "Ongoing" },
            { name: "Robert Brown", issue: "Family Issues", status: "Resolved" },
            { name: "Emily Johnson", issue: "Bullying", status: "Ongoing" },
        ];

        // Populate Stats
        document.getElementById("students-served").textContent = stats.studentsServed;
        document.getElementById("sessions-conducted").textContent = stats.sessionsConducted;
        document.getElementById("active-cases").textContent = stats.activeCases;

        // Populate Report Table
        const tableBody = document.getElementById("report-table");
        reports.forEach((report, index) => {
            const row = document.createElement("tr");
            row.className = "border-t";

            row.innerHTML = `
                <td class="px-4 py-2 text-gray-600 border border-gray-300">${index + 1}</td>
                <td class="px-4 py-2 text-gray-600 border border-gray-300">${report.name}</td>
                <td class="px-4 py-2 text-gray-600 border border-gray-300">${report.issue}</td>
                <td class="px-4 py-2 text-gray-600 border border-gray-300">${report.status}</td>
            `;

            tableBody.appendChild(row);
        });

        // Chart.js Implementation
        const ctx = document.getElementById('analytics-chart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Students Served', 'Sessions Conducted', 'Active Cases'],
                datasets: [{
                    data: [stats.studentsServed, stats.sessionsConducted, stats.activeCases],
                    backgroundColor: ['#22c55e', '#3b82f6', '#ef4444'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
            },
        });
</script>
<script src="../path/to/flowbite/dist/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>