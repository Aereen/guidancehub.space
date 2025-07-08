<?php
// Start session at the very beginning
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "u406807013_guidancehub";
$password = "GuidanceHub@2025";
$dbname = "u406807013_guidancehub";

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: /src/ControlledData/login.php"); // Redirect to login
    exit;
}

$email = $_SESSION['email']; // Get email from session

// Fetch user profile data using email
$profile = [];
$stmt = $con->prepare("SELECT * FROM user_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
}
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $new_email = trim($_POST['email']);
    $college_dept = trim($_POST['college_dept']);
    $year_level = trim($_POST['year_level']);

    // Ensure no empty fields
    if (!empty($name) && !empty($new_email) && !empty($college_dept) && !empty($year_level)) {
        $stmt = $con->prepare("UPDATE user_profile 
                               SET name = ?, email = ?, college_dept = ?, year_level = ? 
                               WHERE email = ?");
        $stmt->bind_param("sssss", $name, $new_email, $college_dept, $year_level, $email);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Update session email if changed
            if ($new_email !== $email) {
                $_SESSION['email'] = $new_email;
            }
            $success_message = "Profile updated successfully!";
        } else {
            $error_message = "No changes were made to your profile.";
        }

        $stmt->close();
    } else {
        $error_message = "All fields are required.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: /index.php");
    exit;
}

// Close connection at the end
$con->close();
?>




<!doctype html>
<html>
<head>
<title>GuidanceHub | Profile</title>
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

<!--TOP NAVIGATION BAR-->
<header class="fixed top-0 left-0 z-50 w-full bg-teal-600 shadow-md">
    <div class="flex px-3 py-4 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between w-full mx-auto max-w-7xl">

            <!-- LOGO --> 
            <div class="flex items-center mx-5">
                <img src="/src/images/UMAK-CGCS-logo.png" alt="CGCS Logo" class="w-10 h-auto md:w-14">
                <span class="ml-4 font-semibold tracking-wide text-white md:text-2xl">GuidanceHub</span>
            </div>

            <!-- Hamburger Icon (Mobile) -->
            <button id="menu-toggle" class="text-2xl text-white md:hidden focus:outline-none">
                <i class="fa-solid fa-bars"></i>
            </button>

            <!-- Navigation Links (Desktop) -->
            <nav id="nav-menu" class="items-center hidden space-x-6 md:flex">
                <a href="dashboard.php" class="text-white hover:text-gray-300">Dashboard</a>
                <a href="library.php" class="text-white hover:text-gray-300">Library</a>
                <a href="profile.php" class="text-white hover:text-gray-300">Profile</a>

                <!-- Messages Icon -->
                <div class="relative">
                    <button id="messageButton" class="text-white hover:text-gray-300 focus:outline-none">
                        <i class="text-2xl fa-solid fa-message"></i>
                        <span id="messageBadge" class="absolute hidden w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full -top-2 -right-2">3</span>
                    </button>
                </div>

                <!-- Notifications Icon -->
                <div class="relative">
                    <button id="notificationButton" class="text-white hover:text-gray-300 focus:outline-none">
                        <i class="text-2xl fa-solid fa-bell"></i>
                        <span id="notificationBadge" class="absolute hidden w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full -top-2 -right-2">0</span>
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
    <div id="mobile-menu" class="flex-col items-center hidden p-4 space-y-4 bg-teal-700 md:hidden">
        <a href="dashboard.php" class="text-white hover:text-gray-300">Dashboard</a>
        <a href="library.php" class="text-white hover:text-gray-300">Library</a>
        <a href="profile.php" class="text-white hover:text-gray-300">Profile</a>
        <a href="?logout=true" class="text-white hover:text-gray-300">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</header>

<!--CONTENT-->
<main class="w-full p-4 mt-20">
    <h4>PROFILE</h4>
        <div>
            <img src="/src/images/UMak-Facade-Admin.jpg" alt="Profile Picture">
            <table>
                <tbody>
                    <tr><td>Name: <?= htmlspecialchars($profile['name']); ?></td></tr>
                    <tr><td>Email: <?= htmlspecialchars($profile['email']); ?></td></tr>
                    <tr><td>College Dept: <?= htmlspecialchars($profile['college_dept']); ?></td></tr>
                    <tr><td>Year Level: <?= htmlspecialchars($profile['year_level']); ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Display success or error messages -->
    <?php if (isset($success_message)): ?>
        <p class="success"><?= $success_message; ?></p>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <p class="error"><?= $error_message; ?></p>
    <?php endif; ?>

    <!-- Profile update form -->
    <form action="profile.php" method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($profile['name']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($profile['email']); ?>" required>

        <label for="college_dept">College Dept:</label>
        <input type="text" id="college_dept" name="college_dept" value="<?= htmlspecialchars($profile['college_dept']); ?>" required>

        <label for="year_level">Year Level:</label>
        <input type="text" id="year_level" name="year_level" value="<?= htmlspecialchars($profile['year_level']); ?>" required>

        <button type="submit">Update Profile</button>
    </form>
</main>

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
<footer class="w-full" style="background-color: #1EB0A9">
    <div class="w-full max-w-screen-xl p-4 py-6 mx-auto lg:py-8 dark:text-gray-800">
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

//Highlighted Appointment Schedule Calendar
    function showTooltip(element, text) {
        let tooltip = element.querySelector('.tooltip');
        tooltip.innerHTML = text;
        tooltip.classList.remove('hidden');
    }

    function hideTooltip(element) {
        let tooltip = element.querySelector('.tooltip');
        tooltip.classList.add('hidden');
    }
</script>
<script src="../path/to/flowbite/dist/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>