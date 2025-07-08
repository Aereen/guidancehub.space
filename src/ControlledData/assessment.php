<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Database connection
$servername = "localhost";
$username = "u406807013_guidancehub";
$password = "GuidanceHub@2025";
$dbname = "u406807013_guidancehub";

$con = new mysqli($servername, $username, $password, $dbname);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Require login
if (!isset($_SESSION['email'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user = [
    'name' => $_SESSION['name'] ?? '',
    'email' => $_SESSION['email'] ?? ''
];

// Function to generate unique Ticket ID
function generateTicketID($con) {
    $dateCode = date("Ym");
    $query = "SELECT COUNT(*) AS total FROM assessments WHERE DATE_FORMAT(created_at, '%Y%m') = '$dateCode'";
    $result = $con->query($query);

    if ($result) {
        $row = $result->fetch_assoc();
        $count = $row['total'] + 1;
        return "AS-" . $dateCode . "-" . str_pad($count, 3, '0', STR_PAD_LEFT);
    } else {
        die("Query failed: " . $con->error);
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $required_fields = ['student_name', 'student_email', 'college', 'test_type', 'schedule_date', 'schedule_time'];
        
        // Check for missing required fields
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Missing required field: " . ucfirst(str_replace('_', ' ', $field)));
            }
        }

        // Generate ticket ID (assumed to be a function that generates a unique ticket)
        $ticket_id = generateTicketID($con);

        // SQL Insert query to add data to the database
        $sql = "INSERT INTO assessments (ticket_id, student_name, student_email, college, test_type, schedule_date, schedule_time)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $con->prepare($sql);

        // Bind parameters for the SQL query
        $stmt->bind_param(
            "sssssss",  // Seven string parameters
            $ticket_id,
            $_POST['student_name'],
            $_POST['student_email'],  // Missing student_email in previous version
            $_POST['college'],         // Add college field
            $_POST['test_type'],
            $_POST['schedule_date'],
            $_POST['schedule_time']
        );
        
        // Execute the prepared statement
        $stmt->execute();
        $stmt->close();

        // Send email confirmation using PHPMailer
        $mail = new PHPMailer(true);
        $userEmail = $_POST['student_email']; // Use the email submitted in the form
        $userName = $_POST['student_name'];

        try {
            // Configure PHPMailer settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'guidancehub01@gmail.com';
            $mail->Password   = 'mkqn ecje evor lgdj'; // Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('guidancehub01@gmail.com', 'GuidanceHub');
            $mail->addAddress($userEmail, $userName);

            $mail->isHTML(true);
            $mail->Subject = 'Assessment Schedule Confirmation';
            $mail->Body    = "
                <p>Hi <b>$userName</b>,</p>
                <p>Your assessment schedule has been successfully created. Here are the details:</p>
                <ul>
                    <li><b>Ticket ID:</b> $ticket_id</li>
                    <li><b>Assessment Type:</b> {$_POST['test_type']}</li>
                    <li><b>Schedule:</b> {$_POST['schedule_date']} at {$_POST['schedule_time']}</li>
                    <li><b>College:</b> {$_POST['college']}</li>
                </ul>
                <p>Please check your portal for updates or contact us if you have questions.</p>
                <p><i>GuidanceHub Team</i></p>
            ";

            // Attempt to send the email
            $mail->send();
        } catch (Exception $e) {
            // Log error if the email couldn't be sent
            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }

        // Success message and redirection
        echo "<script>alert('Assessment scheduled successfully!'); window.location.href='/src/student/dashboard.php';</script>";
    } catch (Exception $e) {
        // Handle any errors and alert the user
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location.href='/src/ControlledData/assessment.php';</script>";
    }
}

// When logout is requested
if (isset($_GET['logout'])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: /index.php"); // Redirect after logout
    exit;
}

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
        .blue-1:hover {
            color: #111c4e;
        }
        .blue-2:hover {
            color: #618dc2;
        }

    </style>
</head>
<body class="bg-gray-100">

<!--HEADER-->
<header class="fixed top-0 left-0 z-50 w-full py-4 shadow-xl marcellus-regular" style="background-color: #111c4e">
    <div class="flex items-center justify-between px-4 mx-auto container-fluid md:px-8">
        <!-- Logo -->
        <div class="flex items-center space-x-3">
            <a href="https://www.umak.edu.ph/" class="flex items-center space-x-3">
                <img src="/src/images/UMAK-Logo.png" alt="UMAK Logo" class="w-10 h-auto md:w-14">
                <span class="font-semibold tracking-wide text-white md:text-2xl">University of Makati</span>
            </a>
        </div>

        <!-- Hamburger Icon -->
        <button id="menu-toggle" class="block md:hidden">
            <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
            </svg>
        </button>

        <!-- Navigation Menu -->
        <nav id="menu" class="hidden md:flex">
            <ul class="flex flex-col space-y-2 text-lg font-semibold text-wshite md:flex-row md:space-x-10 md:space-y-0">
                <li><a href="/src/student/dashboard.php" class="text-white blue-2">Dashboard</a></li>
                <li><a href="/src/ControlledData/appointment.php" class="text-white blue-2">Appointment</a></li>
                <li><a href="/src/ControlledData/assessment.php" class="text-white blue-2">Assessment</a></li>
                <li><a href="?logout=true" class="text-white hover:text-gray-300">
                    <i class="text-xl fa-solid fa-right-from-bracket"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>

<!--CONTENT-->
<main class="flex items-center justify-center mt-24"> 
    <div class="w-full max-w-4xl p-6">
        <h2 class="text-2xl font-bold text-center">Assessment Scheduling</h2>
        <div class="max-w-4xl mx-auto my-5 mt-6">

            <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-lg">
                <div class="float-left m-5 text-xl font-semibold">
                    <a href="src/student/dashboard.php">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>
                <form action="assessment.php" method="POST">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="student_name" class="block font-medium text-gray-700 text-md">Name (First Name-MI-Last Name)</label>
                            <input type="text" id="student_name" name="student_name" required class="w-full p-2 mb-3 border rounded" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                        </div>
                        <div>
                            <label for="student_email" class="block font-medium text-gray-700 text-md">UMak Email Address</label>
                            <input type="email" id="student_email" name="student_email" required class="w-full p-2 mb-3 border rounded" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                    </div>

                    <div>
                        <label for="college" class="block font-medium text-gray-700 text-md">College/Institute</label>
                        <select id="college" name="college" required class="w-full p-2 mb-3 border rounded">
                            <option value="" disabled selected>Select College</option>
                            <option value="CBFS">College of Business and Financial Science (CBFS)</option>
                            <option value="CCIS">College of Computing and Information Sciences (CCIS)</option>
                            <option value="CCSE">College of Construction Sciences and Engineering (CCSE)</option>
                            <option value="CGPP">College of Governance and Public Policy (CGPP)</option>
                            <option value="CHK">College of Human Kinetics (CHK)</option>
                            <option value="CITE">College of Innovative Teacher Education (CITE)</option>
                            <option value="CTM">College of Technology Management (CTM)</option>
                            <option value="CTHM">College of Tourism and Hospitality Management (CTHM)</option>
                            <option value="IOA">Institute of Accountancy (IOA)</option>
                            <option value="IAD">Institute of Arts and Design (IAD)</option>
                            <option value="IIHS">Institute of Imaging Health Sciences (IIHS)</option>
                            <option value="ION">Institute of Nursing (ION)</option>
                            <option value="IOP">Institute of Pharmacy (IOP)</option>
                            <option value="IOPsy">Institute of Psychology (IOPsy)</option>
                            <option value="ISDNB">Institute of Social Development and Nation Building (ISDNB)</option>
                            <option value="HSU">Higher School ng UMak (HSU)</option>
                            <option value="SOL">School of Law (SOL)</option>
                        </select>
                    </div>

                    <div>
                        <label for="test_type" class="block font-medium text-gray-700 text-md">Select Test</label>
                        <select id="test_type" name="test_type" required class="w-full p-2 mb-3 border rounded">
                            <option value="" disabled selected>Select Test</option>
                            <option value="Personality">Personality</option>
                            <option value="Traits">Traits</option>
                            <option value="Intelligence">Intelligence</option>
                            <option value="Emotional">Emotional</option>
                            <option value="Aptitude">Aptitude</option>
                            <option value="Career">Career</option>
                            <option value="Behavioral">Behavioral</option>
                        </select>
                    </div>

                    <div>
                        <label for="schedule_date" class="block font-medium text-gray-700 text-md">Schedule Date</label>
                        <input type="date" id="schedule_date" name="schedule_date" required class="w-full p-2 mb-3 border rounded">
                    </div>

                    <div>
                        <label for="schedule_time" class="block font-medium text-gray-700 text-md">Schedule Time</label>
                        <input type="time" id="schedule_time" name="schedule_time" required class="w-full p-2 mb-4 border rounded">
                    </div>

                    <button type="submit" class="w-full p-2 text-white bg-blue-500 rounded">Schedule</button>
                </form>
            </div>
        </div>
    </div>
</main>

<!--FOOTER-->
<footer class="w-full" style="background-color: #111c4e; bottom: 0; left: 0; right: 0;">
    <div class="w-full max-w-screen-xl p-4 py-6 mx-auto lg:py-8 dark:text-gray-800">
        <div class="md:flex md:justify-between">
            <div class="mb-6 md:mb-0">
                <a href="https://flowbite.com/" class="flex items-center">
                    <img src="/src/images/UMAK-CGCS-logo.png" class="h-8 me-3" alt="GuidanceHub Logo" />
                    <span class="font-bold tracking-wide text-white md:text-2xl">GuidanceHub</span>
                </a>
            </div>
            <div class="grid grid-cols-2 gap-8 text-white sm:gap-6 sm:grid-cols-3">
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
        <div class="text-white sm:flex sm:items-center sm:justify-between">
            <span class="text-sm sm:text-center">© 2025 Group 8 | IV-AINS. All Rights Reserved.
            </span>
        </div>
    </div>
</footer>

<script>
function openTab(event, tabId) {
        // Hide all tabs
        document.querySelectorAll(".tab-content").forEach(tab => {
            tab.classList.add("hidden");
        });

        // Remove active class from all buttons
        document.querySelectorAll("#tabs button").forEach(button => {
            button.classList.remove("bg-gray-300");
            button.classList.add("bg-gray-200");
        });

        // Show the selected tab
        document.getElementById(tabId).classList.remove("hidden");

        // Highlight the active button
        event.currentTarget.classList.add("bg-gray-300");
        event.currentTarget.classList.remove("bg-gray-200");
    }
</script>
<script src="../path/to/flowbite/dist/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>