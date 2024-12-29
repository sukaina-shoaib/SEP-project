<?php
session_start();
include("Database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];

// Fetch the most recent appointment for the logged-in user
$appointment_query = "SELECT a.token_no, a.receipt_no, a.Day, a.Time, a.payment, d.Name AS doctor_name 
                      FROM Appointments a
                      JOIN Doctor d ON a.DoctorID = d.DoctorID
                      WHERE a.PatientID = '$patient_id'
                      ORDER BY a.token_no DESC LIMIT 1"; // Use token_no to determine the latest record
$appointment_result = mysqli_query($conn, $appointment_query);

$recent_appointment = null;
if (mysqli_num_rows($appointment_result) > 0) {
    $recent_appointment = mysqli_fetch_assoc($appointment_result);
}

// Fetch the recent lab tests for the logged-in user
$test_query = "
    SELECT lt.testno, lt.Date, lt.Time, l.Name AS test_name, l.Amount AS test_amount
    FROM LabTest lt
    JOIN Lab l ON lt.LabID = l.LabID
    WHERE lt.PatientID = '$patient_id'
    ORDER BY lt.Date DESC, lt.Time DESC
";
$test_result = mysqli_query($conn, $test_query);

$labtest_details = [];
if (mysqli_num_rows($test_result) > 0) {
    while ($row = mysqli_fetch_assoc($test_result)) {
        $labtest_details[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .navbar { background-color: #333; padding: 10px; display: flex; justify-content: space-between; align-items: center; color: white; }
        .navbar a { color: white; text-decoration: none; margin: 0 10px; }
        .navbar a:hover { text-decoration: underline; }
        .card { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; }
        .card h2 { margin: 0 0 10px; }
    </style>
</head>
<body>
    <div class="navbar">
        <div>Hospital Management</div>
        <div>
            <a href="appointment.php">Appointment</a>
            <a href="labtest.php">Lab Test</a>
            <a href="reports.php">Reports</a>
            <a href="patient_treatment.php">treatment</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>

    <?php if ($recent_appointment): ?>
        <div class="card">
            <h2>Recent Appointment</h2>
            <p><strong>Doctor:</strong> <?php echo $recent_appointment['doctor_name']; ?></p>
            <p><strong>Token Number:</strong> <?php echo $recent_appointment['token_no']; ?></p>
            <p><strong>Receipt Number:</strong> <?php echo $recent_appointment['receipt_no']; ?></p>
            <p><strong>Day:</strong> <?php echo $recent_appointment['Day']; ?></p>
            <p><strong>Time:</strong> <?php echo $recent_appointment['Time']; ?></p>
            <p><strong>Payment:</strong> $<?php echo $recent_appointment['payment']; ?></p>
            <p><strong>Take a screenshot for future reference</strong></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($labtest_details)): ?>
        <div class="card">
            <h2>Your Recent Lab Tests</h2>
            <?php foreach ($labtest_details as $test): ?>
                <p><strong>Test Name:</strong> <?php echo $test['test_name']; ?></p>
                <p><strong>Test Number:</strong> <?php echo $test['testno']; ?></p>
                <p><strong>Date:</strong> <?php echo $test['Date']; ?></p>
                <p><strong>Time:</strong> <?php echo $test['Time']; ?></p>
                <p><strong>Amount:</strong> $<?php echo $test['test_amount']; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</body>
</html>
