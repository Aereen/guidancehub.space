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
$username = "u406807013_guidancehub";
$password = "GuidanceHub@2025";
$dbname = "u406807013_guidancehub";

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$errors = array();
$otp_sent = false;
$email = ''; // Initialize email variable to retain its value

if (isset($_POST['login_user'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']); 

    if (empty($email)) {
        array_push($errors, "Email is required");
    } elseif (
        !preg_match('/@umak\.edu\.ph$/', $email) &&
        $email !== "guidancehub01@gmail.com" && // Admin
        $email !== "eirene.armilla@gmail.com" && // Counselor
        $email !== "stefgaslang@gmail.com" && // Exception Student
        $email !== "armilla.eirenegrace@gmail.com" // Faculty
    ) {
        array_push($errors, "Only @umak.edu.ph email addresses are allowed, except for authorized users.");
    }

    if (empty($password)) {
        array_push($errors, "Password is required");
    }

    if (count($errors) == 0) {
        $query = "SELECT * FROM users WHERE email='$email'";
        $result = mysqli_query($con, $query);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            $storedPassword = $user['password'];

            // ✅ Match plain or hashed password
            if ($password === $storedPassword || password_verify($password, $storedPassword)) {
                $email = $user['email'];
                $role = $user['role'];

                // Generate OTP and set expiration
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['otp_expiration'] = time() + 300;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;

                // Send OTP
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
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Login OTP';
                    $mail->Body    = "Your OTP is: <b>$otp</b>. This OTP is valid for 5 minutes.";

                    $mail->send();
                    $otp_sent = true;
                } catch (Exception $e) {
                    array_push($errors, "Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                }
            } else {
                array_push($errors, "Incorrect password.");
            }
        } else {
            array_push($errors, "Email not found.");
        }
    }
}



// OTP LOGIN - Verify OTP
if (isset($_POST['verify_otp'])) {
    $entered_otp = mysqli_real_escape_string($con, $_POST['otp']);
    $email = $_SESSION['email'] ?? '';

    // Check if OTP is expired
    if (time() > ($_SESSION['otp_expiration'] ?? 0)) {
        $errors[] = "OTP has expired. Please request a new one.";
    } elseif ($entered_otp == ($_SESSION['otp'] ?? '')) {
        // Fetch user details from the database
        $stmt = $con->prepare("SELECT id_number, name, email, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();

        if ($user_data) {
            $_SESSION['id_number'] = $user_data['id_number'];
            $_SESSION['name'] = $user_data['name'];
            $_SESSION['email'] = $user_data['email'];
            $_SESSION['role'] = $user_data['role'];

            // Clean up OTP session data
            unset($_SESSION['otp'], $_SESSION['otp_expiration'], $_SESSION['redirect']);

            // Redirect to the appropriate dashboard based on the user's role
            switch ($_SESSION['role']) {
                case 'Counselor':
                    header('Location: /src/counselor/dashboard.php');
                    break;
                case 'Faculty':
                    header('Location: /src/ControlledData/referral.php');
                    break;
                case 'Admin':
                    header('Location: /src/admin/dashboard.php');
                    break;
                case 'Student':
                    header('Location: /src/student/dashboard.php');
                    break;
                default:
                    // Optional fallback in case of unexpected role
                    header('Location: /index.php');
            }
            exit();
        } else {
            $errors[] = "User not found.";
        }
    } else {
        $errors[] = "Incorrect OTP.";
    }
}


// Close connection
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login | GuidanceHub</title>
    <link rel="icon" type="images/x-icon" href="/src/images/UMAK-CGCS-logo.png" />
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/95c10202b4.js" crossorigin="anonymous"></script>
</head>
<body>
    <!-- Background Image with overlay -->
    <div class="relative flex items-center justify-center min-h-screen bg-center bg-cover hero"
        style="background-image: url('/src/images/UMak-Facade-Admin.jpg');">
        <div class="absolute inset-0 bg-black opacity-50"></div> <!-- Dark overlay -->

        <!-- Form Container -->
        <div class="relative z-10 flex items-center justify-center w-full p-4">
            <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-md sm:p-8">
                <div class="mb-4 text-xl font-semibold">
                    <a href="/index.php" class="text-gray-700 hover:text-gray-900">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>
                <h2 class="mb-6 text-2xl font-semibold text-center">Login</h2>

                <?php if (count($errors) > 0): ?>
                    <div class="p-3 mb-4 text-red-700 bg-red-100 rounded-md">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form Start -->
                <form action="login.php" method="POST">
                    <div class="space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" class="w-full px-4 py-2 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" id="password" name="password" class="w-full px-4 py-2 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="login_user" class="w-full px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600">Send OTP</button>
                        </div>
                    </div>
                </form>

                <?php if ($otp_sent): ?>
                    <!-- OTP Verification Form -->
                    <form action="login.php" method="POST">
                        <div class="mt-6 space-y-4">
                            <div>
                                <label for="otp" class="block text-sm font-medium text-gray-700">Enter OTP</label>
                                <input type="text" id="otp" name="otp" class="w-full px-4 py-2 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                            </div>

                            <div class="mt-4">
                                <button type="submit" name="verify_otp" class="w-full px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-600">Verify OTP</button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>

                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">Don't have an account? <a href="/src/ControlledData/signup.php" class="text-blue-600 hover:text-blue-800">Sign up here</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>