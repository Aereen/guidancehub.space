<?php
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

// Initialize errors array
$errors = [];

// SIGNUP USER
if (isset($_POST['signup'])) {
    // Receive form data
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $id_number = mysqli_real_escape_string($con, $_POST['id_number']);
    $role = mysqli_real_escape_string($con, $_POST['role']); // This will be either 'Counselor' or 'faculty'
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);

    // Form validation: Ensure required fields are filled
    if (empty($name)) { array_push($errors, "Name is required"); }
    if (empty($email)) { array_push($errors, "Email is required"); }
    if (empty($id_number)) { array_push($errors, "ID Number is required"); }
    if (empty($role)) { array_push($errors, "Role is required"); }
    if (empty($password)) { array_push($errors, "Password is required"); }
    if ($password !== $confirm_password) { array_push($errors, "Passwords do not match"); }

    // Check if email already exists
    $user_check_query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($con, $user_check_query);
    
    if (!$result) {
        die("Query failed: " . mysqli_error($con)); // Debugging query error
    }

    $user = mysqli_fetch_assoc($result);

    if ($user) { // If user exists
        if ($user['email'] === $email) {
            array_push($errors, "Email already exists");
        }
    }

    // Register user if no errors
    if (count($errors) == 0) {
        // Hash the password before storing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the database
        $query = "INSERT INTO users (name, email, id_number, role, password) 
                    VALUES('$name', '$email', '$id_number', '$role', '$hashed_password')";

        if (mysqli_query($con, $query)) {
            $_SESSION['email'] = $email;
            $_SESSION['success'] = "You are now registered";
            header('location: /src/ControlledData/login.php'); // Redirect to login page after successful registration
            exit();
        } else {
            // If query fails
            die("Error in inserting user: " . mysqli_error($con));
        }
    }
}

// Close the connection at the end of the script
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up | GuidanceHub</title>
    <link rel="icon" type="images/x-icon" href="/src/images/UMAK-CGCS-logo.png" />
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://kit.fontawesome.com/95c10202b4.js" crossorigin="anonymous"></script>

    <!-- JavaScript for validating password and confirm password match -->
    <script>
        function validatePasswords() {
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('confirm_password').value;
            var message = document.getElementById('password-message');

            // Check password length and complexity
            var passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{16,21}$/;

            if (!passwordPattern.test(password)) {
                message.style.color = 'red';
                message.textContent = 'Password must be 16-21 characters long, with at least one uppercase, one lowercase, one number, and one special character.';
                return false;
            }

            if (password !== confirmPassword) {
                message.style.color = 'red';
                message.textContent = 'Passwords do not match!';
                return false;
            }

            message.style.color = 'green';
            message.textContent = 'Passwords match!';
            return true;
        }

        // Function to toggle password visibility
        function togglePassword() {
            var passwordField = document.getElementById('password');
            var confirmPasswordField = document.getElementById('confirm_password');
            var type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;
            confirmPasswordField.type = type;
        }
    </script>
</head>
<body>
    <!-- Background Image with overlay -->
    <div class="relative flex items-center justify-center bg-center bg-cover hero"
        style="background-image: url('/src/images/UMak-Facade-Admin.jpg'); height: 100vh;">
        <div class="absolute inset-0 bg-black opacity-50"></div> <!-- Dark overlay -->

        <!-- Form Container -->
        <div class="relative z-10 flex items-center justify-center w-full h-full">
            <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-md">
                <!-- Back Link -->
                <div class="float-left mb-4 text-xl font-semibold">
                    <a href="/index.php">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>

                <!-- Sign Up Heading -->
                <h2 class="mb-6 text-2xl font-semibold text-center">Sign Up</h2>

                <!-- Sign Up Form -->
                <form action="signup.php" method="POST" onsubmit="return validatePasswords()">
                        <!-- Name -->
                        <div class="mb-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" id="name" name="name" class="w-full px-4 py-2 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                        </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" class="w-full px-4 py-2 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                    </div>

                    <div class="mb-4 flex flex-col md:flex-row md:items-center md:space-x-6">
                        <!-- ID Number -->
                        <div class="w-full md:w-1/2">
                            <label for="id_number" class="block text-sm font-medium text-gray-700">ID Number</label>
                            <input type="text" id="id_number" name="id_number" class="w-full px-4 py-2 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                        </div>

                        <!-- Role Selection -->
                        <div class="w-full md:w-1/2">
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <div class="flex text-center space-x-4 mt-1">
                                <div>
                                    <input type="radio" id="counselor" name="role" value="Counselor" class="mr-2" required>
                                    <label for="counselor" class="text-sm font-medium text-gray-700">Counselor</label>
                                </div>
                                <div>
                                    <input type="radio" id="faculty" name="role" value="Faculty" class="mr-2" required>
                                    <label for="faculty" class="text-sm font-medium text-gray-700">Faculty</label>
                                </div>
                                <div>
                                    <input type="radio" id="student" name="role" value="Student" class="mr-2" required>
                                    <label for="student" class="text-sm font-medium text-gray-700">Student</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Password and Confirm Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" class="w-full px-4 py-2 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                            <button type="button" onclick="togglePassword()" class="absolute text-gray-500 right-3 top-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12c0 3.866-3.134 7-7 7S1 15.866 1 12s3.134-7 7-7 7 3.134 7 7zm2 0c0-4.418-3.582-8-8-8s-8 3.582-8 8 3.582 8 8 8 8-3.582 8-8z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                            <button type="button" onclick="togglePassword()" class="absolute text-gray-500 right-3 top-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12c0 3.866-3.134 7-7 7S1 15.866 1 12s3.134-7 7-7 7 3.134 7 7zm2 0c0-4.418-3.582-8-8-8s-8 3.582-8 8 3.582 8 8 8 8-3.582 8-8z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Password validation message -->
                    <div id="password-message" class="mt-2 text-sm text-gray-500"></div>

                    <!-- Submit Button -->
                    <div class="flex justify-center mt-4">
                        <button type="submit" name="signup" class="w-full px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600">Sign Up</button>
                    </div>
                </form>

                <!-- Sign Up Redirect Link -->
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">Already have an account? <a href="/src/ControlledData/login.php" class="text-blue-600 hover:text-blue-800">Log in here</a></p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
