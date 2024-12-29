<?php
session_start();
include("Database.php");

// Check if the doctor is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit;
}

$doctor_id = $_SESSION['user_id']; // Doctor's ID from session
$doctor_name = $_SESSION['user_name']; // Doctor's name from session

// Fetch all appointments for the logged-in doctor
$appointment_query = "
    SELECT 
        appointments.token_no,
        appointments.receipt_no,
        appointments.day AS appointment_day,
        appointments.time AS appointment_time,
        appointments.payment,
        appointments.appointment_date,
        patient.Name AS patient_name,
        patient.PatientID
    FROM 
        appointments
    JOIN 
        patient ON appointments.PatientID = patient.PatientID
    WHERE 
        appointments.DoctorID = '$doctor_id'
    ORDER BY 
        appointments.appointment_date ASC, appointments.time ASC";

$result = mysqli_query($conn, $appointment_query);
if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Appointments</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #333;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            padding: 10px 20px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 14px 20px;
            display: inline-block;
        }
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
        h2 {
            text-align: center;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        table th {
            background-color: #f4f4f4;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="physiotherapist_treatment.php">Treatment</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="content">
        <h2>Appointments for Dr. <?php echo htmlspecialchars($doctor_name); ?></h2>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Token No</th>
                        <th>Receipt No</th>
                        <th>Patient Name</th>
                        <th>Patient ID</th>
                        <th>Appointment Date</th>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['token_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['receipt_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['PatientID']); ?></td>
                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['appointment_day']); ?></td>
                            <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                            <td><?php echo htmlspecialchars($row['payment']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No appointments found for this doctor.</p>
        <?php endif; ?>
    </div>
</body>
</html>
