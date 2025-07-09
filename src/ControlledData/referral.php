<?php
session_start();

// Enable error reporting for debugging
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

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);

// Check connection
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
    $query = "SELECT COUNT(*) AS total FROM referrals WHERE DATE_FORMAT(created_at, '%Y%m') = '$dateCode'";
    $result = $con->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $count = $row['total'] + 1;
        return "RF-" . $dateCode . "-" . str_pad($count, 3, '0', STR_PAD_LEFT);
    } else {
        die("Query failed: " . $con->error);
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $referrer_name = $con->real_escape_string($_POST['referrer_name']);
    $position = $con->real_escape_string($_POST['position']);
    $college_dept = $con->real_escape_string($_POST['college_dept']);
    $ticket_id = generateTicketID($con);
    $student_name = $con->real_escape_string($_POST['student_name']);
    $college = $con->real_escape_string($_POST['college']);
    $reason = $con->real_escape_string($_POST['reason']);
    $terms_accepted = isset($_POST['terms']) ? 1 : 0;

    $sql = "INSERT INTO referrals (ticket_id, student_name, college, reason, terms_accepted, referrer_name, position, college_dept) 
            VALUES ('$ticket_id', '$student_name', '$college', '$reason', '$terms_accepted', '$referrer_name', '$position', '$college_dept')";

    if ($con->query($sql) === TRUE) {
        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        $userEmail = $_POST['referrer_email'];
        $userName = $_POST['referrer_name'];

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
            $mail->Subject = "Referral Submitted: $ticket_id";
            $mail->Body    = "
                <p>Good Day {$user['name']},</p>
                <p>Your referral has been successfully submitted with the following details:</p>
                <ul>
                    <li><strong>Ticket ID:</strong> $ticket_id</li>
                    <li><strong>Student Name:</strong> $student_name</li>
                    <li><strong>College:</strong> $college</li>
                    <li><strong>Reason:</strong> $reason</li>
                </ul>
                <p>Thank you for using GuidanceHub.</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        echo "<script>alert('Referral submitted successfully!'); window.location.href='/src/ControlledData/referral.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
        exit();
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
            color: #111c4e; }
        .blue-2:hover {
            color: #618dc2; }

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
<h2 class="text-2xl font-bold text-center">Referral Form</h2>
    <div class="max-w-4xl mx-auto my-5 mt-6">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-4" id="tabs">
                <button class="px-4 py-2 text-gray-700 bg-gray-300 rounded-md active" onclick="openTab(event, 'tab1')">Referrer Form</button>
                <button class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md" onclick="openTab(event, 'tab2')">Student Information</button>
            </nav>
        </div>
    
        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-lg">
            <div class="float-left text-xl font-semibold">
                <a href="<?= htmlspecialchars($_SERVER['HTTP_REFERER'] ?? '/index.php'); ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
            <form action="referral.php" method="POST" class="mt-4">
    
                <!--Referrer Form Tab-->
                <div id="tab1" class="tab-content">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Referrer Name (First Name-MI-Last Name)</label>
                            <input type="text" name="referrer_name" class="w-full p-2 mt-1 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Position</label>
                            <input type="text" name="position" class="w-full p-2 mt-1 border border-gray-300 rounded-md" required>
                        </div>
                        <div>
                            <label for="college_dept" class="block font-medium text-gray-700 text-md">College/Institute</label>
                                <select id="college_dept" name="college_dept" required
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
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="referrer_email" class="w-full p-2 mt-1 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                    </div>
                        <div class="flex justify-end mt-6">
                            <button class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md" onclick="openTab(event, 'tab2')">Next</button>
                        </div>
                </div>
    
                <!-- Student Information Tab -->
                <div id="tab2" class="hidden tab-content">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="student_name" class="block text-sm font-medium text-gray-700">Student Name (First Name-MI-Last Name)</label>
                            <input type="text" id="student_name" name="student_name" class="w-full p-2 mt-1 border border-gray-300 rounded-md" required>
                        </div>
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
                    </div>
                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Referral
                            <button onclick="openModal()" class="mx-2 text-xl text-black rounded">
                                <i class="fa-solid fa-circle-question"></i>
                            </button>
                           
                           <!-- MODAL -->
                            <div id="modal" class="fixed inset-0 flex items-center justify-center hidden mx-auto bg-gray-800 bg-opacity-50">
                                <div class="w-[90%] md:w-[50%] max-h-[80vh] p-6 bg-white rounded-lg shadow-lg overflow-y-auto flex flex-col justify-center">
                                    <h2 class="mb-4 text-3xl font-bold text-center">Referral Process</h2>
                            
                                    <p class="mb-2 text-2xl font-bold">1. Identification of the Need for Referral</p>
                                    <p class="mb-4 text-lg">
                                        A teacher, staff member, parent, or even the student themselves recognizes the need for counseling services. Common reasons for referral include academic concerns, behavioral issues, emotional distress, or personal/social problems.
                                    </p>
                            
                                    <p class="mb-2 text-2xl font-bold">2. Initiating the Referral</p>
                                    <p class="mb-4 text-lg">
                                        The concerned individual (referrer) fills out the referral form, providing details about the student and the reason for the referral. This form may be submitted online or in person at the guidance office.
                                    </p>
                            
                                    <p class="mb-2 text-2xl font-bold">3. Review and Assessment</p>
                                    <p class="mb-4 text-lg">
                                        The guidance office reviews the referral, assessing the urgency and nature of the concern. If needed, they may gather additional information from teachers, peers, or the student.
                                    </p>
                            
                                    <p class="mb-2 text-2xl font-bold">4. Scheduling an Appointment</p>
                                    <p class="mb-4 text-lg">
                                        If the referral is deemed necessary, a counselor will be assigned to the case, and an appointment will be scheduled. The student will be notified of the details.
                                    </p>
                            
                                    <p class="mb-2 text-2xl font-bold">5. Counseling Session</p>
                                    <p class="mb-4 text-lg">
                                        The student attends the counseling session where they receive support, guidance, and interventions tailored to their needs.
                                    </p>
                            
                                    <p class="mb-2 text-2xl font-bold">6. Follow-Up and Monitoring</p>
                                    <p class="mb-4 text-lg">
                                        After the session, follow-up meetings may be conducted to monitor progress and ensure that the student receives continuous support.
                                    </p>
                            
                                    <div class="flex justify-center">
                                        <button onclick="closeModal()" class="px-6 py-2 text-white bg-red-500 rounded-lg hover:bg-red-600">Close</button>
                                    </div>
                                </div>
                            </div>
    
    
                        </label>
                        <textarea id="reason" name="reason" rows="4" class="w-full p-2 mt-1 border border-gray-300 rounded-md" required></textarea>
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
                            <button type="submit" class="px-6 py-3 text-white bg-blue-500 rounded-md hover:bg-blue-600">
                                Submit Referral
                            </button>
                        </div>
                </div>
            </form>
        </div>
    </div>
</div>
</main>

<!--FOOTER-->
<footer class="w-full" style="background-color: #111c4e;">
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

    function openModal() {
        document.getElementById('modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
    }
</script>
<script src="../path/to/flowbite/dist/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>