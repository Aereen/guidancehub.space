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
$username = "root";
$password = "";
$dbname = "guidancehub";

// Create DB connection
$con = new mysqli($servername, $username, $password, $dbname);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user = [
    'name' => $_SESSION['name'] ?? '',
    'email' => $_SESSION['email'] ?? ''
];

// Generate unique Ticket ID
function generateTicketID($con) {
    $dateCode = date("Ym");
    $query = "SELECT COUNT(*) AS total FROM appointments WHERE DATE_FORMAT(created_at, '%Y%m') = '$dateCode'";
    $result = $con->query($query);
    $row = $result->fetch_assoc();
    $count = $row['total'] + 1;
    return "CS-" . $dateCode . "-" . str_pad($count, 3, '0', STR_PAD_LEFT);
}

// Handle appointment form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_appointment'])) {
    try {
        $ticket_id = generateTicketID($con);

        $feelings = isset($_POST['feelings']) ? implode(", ", $_POST['feelings']) : '';
        if (!empty($_POST['feelings_other'])) {
            $feelings .= (!empty($feelings) ? ", " : "") . $_POST['feelings_other'];
        }

        $sql = "INSERT INTO appointments (ticket_id, student_name, student_email, college, year_level, section, feelings, need_counselor, counseling_type, first_date, first_time, second_date, second_time)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $con->error);
        }

        $stmt->bind_param(
            "sssssssssssss",
            $ticket_id,
            $_POST['student_name'],
            $_POST['student_email'],
            $_POST['college'],
            $_POST['year_level'],
            $_POST['section'],
            $feelings,
            $_POST['need_counselor'],
            $_POST['counseling_type'],
            $_POST['first_date'],
            $_POST['first_time'],
            $_POST['second_date'],
            $_POST['second_time']
        );
        $stmt->execute();
        $stmt->close();

        // Prepare email
        $mail = new PHPMailer(true);
        $userEmail = $_POST['student_email'];
        $userName = $_POST['student_name'];

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'guidancehub01@gmail.com';
            $mail->Password   = 'mkqn ecje evor lgdj'; // App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('guidancehub01@gmail.com', 'GuidanceHub');
            $mail->addAddress($userEmail, $userName);

            $mail->isHTML(true);
            $mail->Subject = 'Counseling Appointment Scheduled';
            $mail->Body    = "
                <p>Hi <b>$userName</b>,</p>
                <p>Your counseling appointment has been successfully scheduled. Here are the details:</p>
                <ul>
                    <li><b>Ticket ID:</b> $ticket_id</li>
                    <li><b>Preferred Date/Time:</b> {$_POST['first_date']} at {$_POST['first_time']}</li>
                    <li><b>Alternate Date/Time:</b> {$_POST['second_date']} at {$_POST['second_time']}</li>
                </ul>
                <p>We’ll get back to you shortly. Thank you!</p>
                <p><i>GuidanceHub Team</i></p>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Email error: " . $mail->ErrorInfo);
            // You can optionally alert the user: email not sent
        }

        echo "<script>
            alert('Appointment scheduled successfully! Log in to check your appointment status.');
            window.location.href='/src/student/dashboard.php';
        </script>";

    } catch (Exception $e) {
        echo "<script>
            alert('Error scheduling appointment: " . addslashes($e->getMessage()) . "');
            window.location.href='/src/ControlledData/appointment.php';
        </script>";
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
<title> GuidanceHub | Appointmennt </title>
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
<main class="mt-28">
<h2 class="mb-6 text-2xl font-bold text-center">Appointment Scheduling</h2>
    <div class="max-w-4xl mx-auto my-5 mt-6">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-4" id="tabs">
                <button class="px-4 py-2 text-gray-700 bg-gray-300 rounded-md active" onclick="openTab(event, 'tab1')">Student Information</button>
                <button class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md" onclick="openTab(event, 'tab2')">Appointment Details</button>
            </nav>
        </div>

        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-lg">
            <div class="float-left text-xl font-semibold">
                <a href="src/student/dashboard.php">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
            <form action="appointment.php" method="POST" class="mt-4">
                <!-- Student Information Form Tab -->
                <div id="tab1" class="tab-content">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Name -->
                        <div>
                            <label for="student_name" class="block font-medium text-gray-700 text-md">Name (First Name-MI-Last Name)</label>
                            <input type="text" id="student_name" name="student_name" required
                                class="w-full p-2 mt-1 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500"
                                placeholder="juan P. Dela Cruz"
                                value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                        </div>
                        
                        <!-- Email -->
                        <div>
                            <label for="student_email" class="block font-medium text-gray-700 text-md">UMak Email Address</label>
                            <input type="email" id="student_email" name="student_email" required
                                class="w-full p-2 mt-1 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500"
                                placeholder="@umak.edu.ph"
                                value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>

                        <!-- College Selection -->
                        <div>
                            <label for="college" class="block font-medium text-gray-700 text-md">College/Institute</label>
                            <select id="college" name="college" required
                                class="w-full p-2 mt-1 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500">
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

                        <!-- Year Level and Section -->
                        <div>
                            <label for="year_level" class="block font-medium text-gray-700 text-md">Year Level</label>
                            <select id="year_level" name="year_level" required
                                class="w-full p-2 mt-1 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500">
                                <option value="" disabled selected>Select Year</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                                <option value="5th Year">5th Year</option>
                            </select>
                        </div>
                        <div>
                            <label for="section" class="block font-medium text-gray-700 text-md">Section</label>
                            <input type="text" id="section" name="section" required
                                class="w-full p-2 mt-1 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500"
                                placeholder="AINS">
                        </div>
                    </div>
                        <div class="flex justify-end mt-6">
                            <button class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md" onclick="openTab(event, 'tab2')">Next</button>
                        </div>
                </div>

                <!-- Appointment Details Tab -->
                <div id="tab2" class="hidden tab-content">
                    <div class="grid grid-cols-1 gap-6">
                    
                    <!-- Feelings Checkboxes -->
                    <div>
                        <label id="feelings-label" class="block font-medium text-gray-700 text-md">
                            How are you (or how are you feeling) right now? Please check all that apply *
                        </label>
                        <div id="feelings-group" class="grid grid-cols-2 gap-2 mt-2 md:grid-cols-3" aria-labelledby="feelings-label">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="feelings[]" value="Excited"> 😄 Excited
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="feelings[]" value="Happy"> 😂 Happy
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="feelings[]" value="Sad"> 😔 Sad
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="feelings[]" value="Scared"> 😨 Scared
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="feelings[]" value="Angry"> 😠 Angry
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="feelings[]" value="Confused"> 😵 Confused
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="feelings[]" value="Burned Out"> 🥵 Burned Out
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="feelings[]" value="Calm"> 😌 Calm
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="feelings[]" value="Struggling"> 😣 Struggling
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="feelings[]" value="Hopeful"> 😇 Hopeful
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="feelings[]" value="Need a hug"> 🤗 Need a hug
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="feelings[]" value="Stuck and Unsure"> 🤨 Stuck and Unsure
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="feelings[]" value="Numb"> 😶 Numb
                            </label>
                        </div>

                            <input type="text" name="feelings_other" placeholder="Other..." class="w-full p-2 mt-2 border rounded-md">
                        </div>

                    <!-- Need to Talk to Counselor & Counseling Type in Same Row -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Need to Talk to Counselor -->
                        <div>
                            <label class="block font-medium text-gray-700 text-md">Do you want/need to talk to a Guidance Counselor? *</label>
                            <select name="need_counselor" required class="w-full p-2 mt-1 border border-gray-300 rounded-md">
                                <option value="" disabled selected>Select an option</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>

                        <!-- Counseling Type -->
                        <div>
                            <label class="block font-medium text-gray-700 text-md">What type of counseling session do you prefer? *</label>
                            <select name="counseling_type" required class="w-full p-2 mt-1 border border-gray-300 rounded-md">
                                <option value="" disabled selected>Select a type</option>
                                <option value="Virtual">Virtual (Online) Counseling</option>
                                <option value="In-Person">In-Person (Face-to-Face) Counseling</option>
                            </select>
                        </div>
                    </div>

                    <!-- Available Schedule Section -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- First Available Schedule -->
                        <div>
                            <label class="block font-medium text-gray-700 text-md">Please indicate your first available schedule for a counseling session *</label>
                            <div class="grid grid-cols-2 gap-4 mt-1">
                                <input type="date" name="first_date" required class="w-full p-2 border border-gray-300 rounded-md">
                                <input type="time" name="first_time" required min="08:00" max="17:00" class="w-full p-2 border border-gray-300 rounded-md">
                            </div>
                        </div>

                        <!-- Second Available Schedule -->
                        <div>
                            <label class="block font-medium text-gray-700 text-md">Please indicate your second available schedule for a counseling session *</label>
                            <div class="grid grid-cols-2 gap-4 mt-1">
                                <input type="date" name="second_date" required class="w-full p-2 border border-gray-300 rounded-md">
                                <input type="time" name="second_time" required min="08:00" max="17:00" class="w-full p-2 border border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms" class="ml-2 text-sm text-gray-700">
                            I agree to the <a href="policy.php" class="text-blue-500 underline">Data Privacy Policy</a> and
                            <a href="terms.php" class="text-blue-500 underline">Terms and Conditions</a>.
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-between mt-4">
                        <button class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md" onclick="openTab(event, 'tab1')">Back</button>
                        <button type="submit" name="submit_appointment" class="px-6 py-3 text-white bg-blue-500 rounded-md hover:bg-blue-600">
                            Submit
                        </button>
                    </div>
                </div>
            </form>
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