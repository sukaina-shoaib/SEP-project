<?php
include("database.php");

$patientID = null;
$name = $email = $cnic = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $cnic = $_POST['cnic'];
    $gender = $_POST['gender'];
    $password = $_POST['password'];

    // Insert data into the Patient table
    $query = "INSERT INTO Patient (Name, Email, PhoneNumber, CNIC, Gender, Password) 
              VALUES ('$name', '$email', '$phone', '$cnic', '$gender', '$password')";

    if (mysqli_query($conn, $query)) {
        // Fetch PatientID based on the CNIC
        $fetchQuery = "SELECT PatientID FROM Patient WHERE CNIC = '$cnic'";
        $result = mysqli_query($conn, $fetchQuery);
        $patient = mysqli_fetch_assoc($result);
        $patientID = $patient['PatientID'];
    } else {
        echo "Error: " . mysqli_error($conn);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        .form-wrapper, .card { max-width: 400px; margin: auto; padding: 20px; }
        .card { border: 1px solid #ccc; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        h2, p { text-align: center; }
    </style>
</head>
<body>
    <div class="form-wrapper">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($patientID)): ?>
            <div class="card">
                <h2>Signup Successful!</h2>
                <p><strong>Patient ID:</strong> <?= htmlspecialchars($patientID) ?></p>
                <p><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
                <p><strong>CNIC:</strong> <?= htmlspecialchars($cnic) ?></p>
                <p>Please save this information for future reference.</p>
                <!-- Button or Link to go to the login page -->
                <p><a href="login.php">Go to Login Page</a></p> <!-- Link to login page -->
                <!-- Or you could use a button -->
                <!-- <p><button onclick="window.location.href='login.php'">Go to Login Page</button></p> -->
            </div>
        <?php else: ?>
            <h2>Sign Up</h2>
            <form method="POST" action="">
                <input type="text" name="name" placeholder="Enter your name" required><br>
                <input type="email" name="email" placeholder="Enter your email" required><br>
                Date of birth: <input type="date" name="date_of_birth" required><br>
                <input type="tel" name="phone" placeholder="Enter your phone number" required><br>
                <input type="text" name="cnic" placeholder="Enter your CNIC" required><br>
                <div>
                    <label>Gender:</label>
                    <input type="radio" name="gender" value="Male" required> Male
                    <input type="radio" name="gender" value="Female" required> Female
                </div>
                <input type="password" name="password" placeholder="Enter your password" required><br>
                <button type="submit">Sign Up</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
