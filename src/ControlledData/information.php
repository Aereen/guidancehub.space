<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include PHPMailer and FPDF
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'fpdf/fpdf.php'; // make sure this path matches your project

// Database connection
$servername = "localhost";
$username = "u406807013_guidancehub";
$password = "GuidanceHub@2025";
$dbname = "u406807013_guidancehub";

// DB Connection
$con = new mysqli($servername, $username, $password, $dbname);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

if (!isset($_SESSION['email'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fields = [
        'student_name', 'student_number', 'student_email', 'student_contact', 'student_birthdate', 
        'student_age', 'student_gender', 'civil_status', 'address', 'religion', 'religion_specify',
        'college_dept', 'year_level', 'elementary', 'elementary_year', 'junior_high', 'junior_year',
        'senior_high', 'senior_year', 'college_name', 'college_year', 'national_exam', 'board_exam',
        'spouse_name', 'date_marriage', 'place_marriage', 'spouse_occupation', 'spouse_employer'
    ];

    $data = [];
    foreach ($fields as $field) {
        $data[$field] = $_POST[$field] ?? '';
    }

    // Save to DB
    $sql = "INSERT INTO individual_inventory (" . implode(',', $fields) . ") VALUES (" . str_repeat('?,', count($fields)-1) . "?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($fields)), ...array_values($data));
    $stmt->execute();
    $stmt->close();

    // Generate PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Individual Inventory Form',0,1,'C');
    $pdf->SetFont('Arial','',12);
    foreach ($data as $key => $value) {
        $pdf->Cell(0,10,ucwords(str_replace('_',' ', $key)) . ': ' . $value,0,1);
    }

    $pdfFile = 'IndividualInventory_' . $data['student_number'] . '.pdf';
    $pdf->Output('F', $pdfFile); // Save to file

    // Send Email with Attachment
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'guidancehub01@gmail.com';
        $mail->Password   = 'mkqn ecje evor lgdj';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('guidancehub01@gmail.com', 'GuidanceHub');
        $mail->addAddress($data['student_email'], $data['student_name']);
        $mail->isHTML(true);
        $mail->Subject = 'Your Individual Inventory Submission';
        $mail->Body    = "
            <p>Hello <b>{$data['student_name']}</b>,</p>
            <p>Thank you for submitting your individual inventory form. Attached is a PDF copy of your responses.</p>
            <p>Best regards,<br>GuidanceHub Team</p>
        ";
        $mail->addAttachment($pdfFile);

        $mail->send();
        unlink($pdfFile); // Clean up the file after sending
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
    }

    echo "<script>alert('Information submitted and emailed successfully!'); window.location.href='/src/student/dashboard.php';</script>";
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
<title>GuidanceHub | Individual Inventory Form</title>
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

<!--Toast Notification for Data Insertion-->
<div class="bottom-0 p-3 position-fixed end-0" style="z-index: 11">
    <div id="toastMessage" class="text-white border-0 toast align-items-center bg-success" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                Data inserted successfully!
            </div>
            <button type="button" class="m-auto btn-close btn-close-white me-2" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

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
<main class="mt-16">
<div class="relative flex items-center justify-center mb-6">
    <div class="float-left text-xl font-semibold">
        <a href="/src/student/dashboard.php">
            <i class="fa-solid fa-arrow-left mx-5"></i>
        </a>
    </div>
    <h2 class="text-2xl font-bold text-center">INDIVIDUAL INVENTORY FORM</h2>
    <div class="relative ml-2">
        <button class="focus:outline-none" id="popoverButton">
            <svg class="w-6 h-6 text-gray-500 hover:text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v4a1 1 0 001 1h2a1 1 0 100-2h-1V7zm-1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
        </button>
        <div class="absolute z-10 hidden w-64 p-4 mt-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg" id="popoverContent">
            This form is used to collect individual information for inventory purposes. Please fill out all the required fields accurately.
        </div>
    </div>
</div>
    <div class="w-5/6 p-6 mx-auto my-4 bg-white rounded-lg shadow-lg">
        <form action="information.php" method="POST">
            
        <!-- Personal Information Table -->
        <div class="overflow-x-auto">
            <table class="w-full mb-6 border border-collapse border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2 text-lg border border-gray-300" colspan="4">PERSONAL INFORMATION</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <tr class="grid grid-cols-1 gap-2 md:grid-cols-2">
                        <td class="px-4 py-2 border border-gray-300">Name<br>(Last Name, Given Name, Middle Name)</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <input type="text" name="student_name" class="w-full p-2 border rounded" required>
                        </td>
                        <td class="px-4 py-2 border border-gray-300">Student No.</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <input type="text" name="student_number" class="w-full p-2 border rounded" required>
                        </td>
                    </tr>
        
                    <tr class="grid grid-cols-1 gap-2 md:grid-cols-2">
                        <td class="px-4 py-2 border border-gray-300">University Email Address</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <input type="email" name="student_email" class="w-full p-2 border rounded">
                        </td>
                        <td class="px-4 py-2 border border-gray-300">Contact No.</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <input type="number" name="student_contact" class="w-full p-2 border rounded" required>
                        </td>
                    </tr>
        
                    <tr class="grid grid-cols-1 gap-2 md:grid-cols-2">
                        <td class="px-4 py-2 border border-gray-300">Date of Birth</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <input type="date" name="student_birthdate" id="birthdate" class="w-full p-2 border rounded" oninput="calculateAge()" required>
                        </td>
                        <td class="px-4 py-2 border border-gray-300">Age</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <input type="number" name="student_age" id="age" class="w-full p-2 border rounded" required>
                        </td>
                    </tr>
        
                    <tr class="grid grid-cols-1 gap-2 md:grid-cols-2">
                        <td class="px-4 py-2 border border-gray-300">Gender</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <select name="student_gender" class="w-full p-2 border rounded" required>
                                <option>Select Option</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Intersex">Intersex</option>
                            </select>
                        </td>
                        <td class="px-4 py-2 border border-gray-300">Civil Status</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <select name="civil_status" class="w-full p-2 border rounded" required>
                                <option>Select Option</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="widow">Widow</option>
                            </select>
                        </td>
                    </tr>
        
                    <tr class="grid grid-cols-1">
                        <td class="px-4 py-2 border border-gray-300">Address</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <input type="text" name="address" class="w-full p-2 border rounded" required>
                        </td>
                    </tr>
        
                    <tr class="grid grid-cols-1 gap-2 md:grid-cols-2">
                        <td class="px-4 py-2 border border-gray-300">Religion</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <select name="religion" id="religion" class="w-full p-2 border rounded" onchange="toggleSpecifyInput()" required>
                                <option value="">Select Option</option>
                                <option value="catholic">Roman Catholic</option>
                                <option value="muslim">Muslim</option>
                                <option value="iglesia">Iglesia ni Cristo</option>
                                <option value="atheist">Atheist</option>
                                <option value="others">Others</option>
                            </select>
                            <input type="text" name="religion_specify" id="religion_specify" class="hidden w-full p-2 mt-2 border rounded" placeholder="Please specify your religion">
                        </td>
                    </tr>
        
                    <tr class="grid grid-cols-1 gap-2 md:grid-cols-2">
                        <td class="px-4 py-2 border border-gray-300">College/Institute</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <select id="college_dept" name="college_dept" class="w-full p-2 border rounded" required>
                                <option value="" disabled selected>Select College</option>
                                <option value="CBFS">College of Business and Financial Science</option>
                                <option value="CCIS">College of Computing and Information Sciences</option>
                                <option value="CCSE">College of Construction Sciences and Engineering</option>
                                <option value="CGPP">College of Governance and Public Policy</option>
                                <option value="CHK">College of Human Kinetics</option>
                                <option value="CITE">College of Innovative Teacher Education</option>
                                <option value="CTM">College of Technology Management</option>
                                <option value="CTHM">College of Tourism and Hospitality Management</option>
                                <option value="IOA">Institute of Accountancy</option>
                                <option value="IAD">Institute of Arts and Design</option>
                                <option value="IIHS">Institute of Imaging Health Sciences</option>
                                <option value="ION">Institute of Nursing</option>
                                <option value="IOP">Institute of Pharmacy</option>
                                <option value="IOPsy">Institute of Psychology</option>
                                <option value="ISDNB">Institute of Social Development and Nation Building</option>
                                <option value="HSU">Higher School ng UMak</option>
                                <option value="SOL">School of Law</option>
                            </select>
                        </td>
                        <td class="px-4 py-2 border border-gray-300">Year Level</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <select id="year_level" name="year_level" class="w-full p-2 border rounded" required>
                                <option value="" disabled selected>Select Year</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                                <option value="5th Year">5th Year</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Educational Background Table -->
        <table class="w-full mb-6 border border-collapse border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2 border border-gray-300" colspan="3">EDUCATIONAL BACKGROUND</th>
                </tr>
            </thead>
            <tbody>
                <tr class="flex flex-wrap">
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3">Elementary</td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3"><input type="text" placeholder="Elementary School Name" name="elementary" class="w-full p-2 border rounded" required></td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3"><input type="number" placeholder="School Year" name="elementary_year" class="w-full p-2 border rounded" required></td>
                </tr>
                <tr class="flex flex-wrap">
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3">Junior High</td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3"><input type="text" placeholder="Junior High School Name" name="junior_high" class="w-full p-2 border rounded" required></td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3"><input type="number" placeholder="School Year" name="junior_year" class="w-full p-2 border rounded" required></td>
                </tr>
                <tr class="flex flex-wrap">
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3">Senior High</td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3"><input type="text" placeholder="Senior High School Name" name="senior_high" class="w-full p-2 border rounded" required></td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3"><input type="number" placeholder="School Year" name="senior_year" class="w-full p-2 border rounded" required></td>
                </tr>
                <tr class="flex flex-wrap">
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3">University</td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3"><input type="text" placeholder="University Name" name="college" class="w-full p-2 border rounded"></td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3"><input type="number" placeholder="School Year" name="college_year" class="w-full p-2 border rounded"></td>
                </tr>
            </tbody>
        </table>

        <!-- Career Background Table -->
        <table class="w-full mb-6 border border-collapse border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2 border border-gray-300" colspan="2">CAREER BACKGROUND</th>
                </tr>
            </thead>
            <tbody>
                <tr class="flex flex-wrap">
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/2">National Exams Passed</td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/2"><input type="text" placeholder="Year Passed" name="national_exam" class="w-full p-2 border rounded"></td>
                </tr>
                <tr class="flex flex-wrap">
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/2">Board Examination</td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/2"><input type="text" placeholder="Year Passed" name="board_exam" class="w-full p-2 border rounded"></td>
                </tr>
            </tbody>
        </table>

        <!-- Siblings' Background Table -->
        <table class="w-full mb-6 border border-collapse border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2 border border-gray-300" colspan="3">SIBLING'S BACKGROUND</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                <tr class="flex flex-wrap">
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3"><input type="text" name="sibling_name_<?= $i ?>" placeholder="Name" class="w-full p-2 border rounded"></td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3"><input type="text" name="sibling_age_<?= $i ?>" placeholder="Age" class="w-full p-2 border rounded"></td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/3"><input type="text" name="sibling_occupation_<?= $i ?>" placeholder="Occupation" class="w-full p-2 border rounded"></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <table class="w-full border border-collapse border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2 border border-gray-300" colspan="4">SPOUSE'S BACKGROUND (if married)</th>
                </tr>
            </thead>
            <tbody>
                <tr class="flex flex-wrap">
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/2">Spouse's Name</td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/2"><input type="text" name="spouse_name" class="w-full p-2 border rounded"></td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/2">Date of Marriage</td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/2"><input type="date" name="date_marriage" class="w-full p-2 border rounded"></td>
                </tr>
                <tr class="flex flex-wrap">
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/2">Place of Marriage</td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/2"><input type="text" name="place_marriage" class="w-full p-2 border rounded"></td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/2">Spouse's Occupation</td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/2"><input type="text" name="spouse_occupation" class="w-full p-2 border rounded"></td>
                </tr>
                <tr class="flex flex-wrap">
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/2">Employer</td>
                    <td class="w-full px-4 py-2 border border-gray-300 lg:w-1/2"><input type="text" name="spouse_employer" class="w-full p-2 border rounded"></td>
        </table>

        <div class="flex items-center mt-4">
            <input type="checkbox" id="terms" name="terms" required>
                <label for="terms" class="ml-2 text-sm text-gray-700">
                    I agree to the <a href="policy.php" class="text-blue-500 underline">Data Privacy Policy</a> and
                    <a href="terms.php" class="text-blue-500 underline">Terms and Conditions</a>.
                </label>
        </div>

        <button type="submit" class="w-40 p-3 m-5 text-white bg-blue-600 rounded-lg hover:bg-blue-700">Submit</button>
        </form>
    </div>
</main>

<!--FOOTER-->
<footer class="w-full" style="background-color: #111c4e">
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
//Menu button
    document.getElementById('popoverButton').addEventListener('click', function() {
        var popoverContent = document.getElementById('popoverContent');
        popoverContent.classList.toggle('hidden');
    });
    
function calculateAge() {
    const birthdate = document.getElementById('birthdate').value;
        if (birthdate) {
            const today = new Date();
            const birthDate = new Date(birthdate);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            document.getElementById('age').value = age;
        }
}

function toggleSpecifyInput() {
        var religionSelect = document.getElementById("religion");
        var specifyInput = document.getElementById("religion_specify");

        if (religionSelect.value === "others") {
            specifyInput.classList.remove("hidden"); // Show input field
            specifyInput.setAttribute("required", "true"); // Make it required
        } else {
            specifyInput.classList.add("hidden"); // Hide input field
            specifyInput.removeAttribute("required"); // Remove required attribute
            specifyInput.value = ""; // Clear input value
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
