<?php
session_start();
include("Database.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$patient_id = $_SESSION['user_id']; // Get the logged-in patient's ID

// Fetch doctors and their details from the database
$doctor_query = "SELECT DoctorID, Name, Day, Time, amount FROM Doctor";
$doctor_result = mysqli_query($conn, $doctor_query);

$doctor_data = [];
if (mysqli_num_rows($doctor_result) > 0) {
    while ($row = mysqli_fetch_assoc($doctor_result)) {
        $doctor_data[$row['DoctorID']] = [
            'name' => $row['Name'],
            'days' => $row['Day'],
            'time' => $row['Time'],
            'amount' => $row['amount']
        ];
    }
}

$appointment_details = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["doctor_id"])) {
    $doctor_id = $_POST["doctor_id"];
    $appointment_date = $_POST["appointment_day"];
    $appointment_time = $_POST["appointment_time"];
    $payment = $doctor_data[$doctor_id]['amount']; // Get the amount from the doctor data

    // Generate unique token and receipt numbers
    $token_no = "T" . time();
    $receipt_no = "R" . time();

    // Insert appointment into the database
    $appointment_query = "INSERT INTO Appointments (token_no, receipt_no, DoctorID, PatientID, day, time, payment, appointment_date) 
                          VALUES ('$token_no', '$receipt_no', '$doctor_id', '$patient_id', '$appointment_date', '$appointment_time', '$payment', '$appointment_date')";

    if (mysqli_query($conn, $appointment_query)) {
        // Fetch the inserted appointment details
        $fetch_query = "SELECT token_no, receipt_no, day, time FROM Appointments 
                        WHERE PatientID = '$patient_id' AND DoctorID = '$doctor_id'";
        $fetch_result = mysqli_query($conn, $fetch_query);

        if (mysqli_num_rows($fetch_result) > 0) {
            $appointment_data = mysqli_fetch_assoc($fetch_result);

            $appointment_details = [
                'token_no' => $appointment_data['token_no'],
                'receipt_no' => $appointment_data['receipt_no'],
                'doctor_name' => $doctor_data[$doctor_id]['name'],
                'appointment_day' => $appointment_data['day'],
                'appointment_time' => $appointment_data['time'],
                'payment' => $payment
            ];
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <style>
        .form-wrapper, .appointment-details { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ccc; }
        h2 { text-align: center; }
        select, input, button { width: 100%; padding: 10px; margin: 10px 0; }
        button { background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        .details { margin: 10px 0; }
    </style>
    <script>
        let doctorData = <?php echo json_encode($doctor_data); ?>;

        function handleDoctorSelection() {
            const doctorId = document.getElementById('doctor_id').value;
            const appointmentDetails = document.getElementById('appointment-details');

            if (doctorId && doctorData[doctorId]) {
                const days = doctorData[doctorId]['days'].split(',').map(day => day.trim());
                const time = doctorData[doctorId]['time'];
                const amount = doctorData[doctorId]['amount'];

                appointmentDetails.innerHTML = `
                    <label>Doctor's Available Days: ${days.join(', ')}</label>
                    <label>Appointment Date</label>
                    <input type="date" id="appointment_day" name="appointment_day" required onchange="validateAppointmentDay('${days.join(',')}')" />
                    <label>Appointment Time</label>
                    <input type="text" name="appointment_time" value="${time}" readonly>
                    <label>Amount</label>
                    <input type="text" value="${amount}" readonly>
                `;
                setAllowedDates(days);
            } else {
                appointmentDetails.innerHTML = '';
            }
        }

        function validateAppointmentDay(validDays) {
            const inputDate = new Date(document.getElementById('appointment_day').value);
            const selectedDay = inputDate.toLocaleString('en-US', { weekday: 'long' });
            const validDaysArray = validDays.split(',');

            if (!validDaysArray.includes(selectedDay)) {
                alert('Selected date does not match the doctor\'s available days. Please choose a valid date.');
                document.getElementById('appointment_day').value = '';
            }
        }

        function setAllowedDates(validDays) {
            const appointmentDayInput = document.getElementById('appointment_day');
            const validDaysArray = validDays.map(day => day.trim());
            const currentDate = new Date();
            const dates = [];

            // Iterate over the next 30 days and find valid days
            for (let i = 0; i < 30; i++) {
                const tempDate = new Date(currentDate);
                tempDate.setDate(tempDate.getDate() + i);
                const tempDay = tempDate.toLocaleString('en-US', { weekday: 'long' });

                if (validDaysArray.includes(tempDay)) {
                    dates.push(tempDate.toISOString().split('T')[0]);
                }
            }

            // Set min, max, and custom valid dates for the calendar input
            appointmentDayInput.min = dates[0];
            appointmentDayInput.max = dates[dates.length - 1];
            appointmentDayInput.setAttribute('data-valid-dates', JSON.stringify(dates));
        }
    </script>
</head>
<body>
    <?php if ($appointment_details): ?>
        <div class="appointment-details">
            <h2>Your Appointment Details</h2>
            <div class="details"><strong>Token Number:</strong> <?php echo $appointment_details['token_no']; ?></div>
            <div class="details"><strong>Receipt Number:</strong> <?php echo $appointment_details['receipt_no']; ?></div>
            <div class="details"><strong>Doctor Name:</strong> <?php echo $appointment_details['doctor_name']; ?></div>
            <div class="details"><strong>Appointment Day:</strong> <?php echo $appointment_details['appointment_day']; ?></div>
            <div class="details"><strong>Appointment Time:</strong> <?php echo $appointment_details['appointment_time']; ?></div>
            <div class="details"><strong>Amount:</strong> <?php echo $appointment_details['payment']; ?></div>
            <a href="index.php">Return to Main Page</a>
        </div>
    <?php else: ?>
        <div class="form-wrapper">
            <h2>Book an Appointment</h2>
            <form method="POST" action="">
                <label for="doctor_id">Select Doctor</label>
                <select id="doctor_id" name="doctor_id" onchange="handleDoctorSelection()" required>
                    <option value="">Select Doctor</option>
                    <?php foreach ($doctor_data as $id => $data) {
                        echo "<option value='$id'>{$data['name']}</option>";
                    } ?>
                </select>

                <div id="appointment-details"></div>

                <button type="submit">Book Appointment</button>
            </form>
        </div>
    <?php endif; ?>
</body>
</html>
