<?php
session_start();
include("Database.php");

// Check if the doctor is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit;
}

$exercise_query = "SELECT e_id, exercise FROM exercise_details";
$exercise_result = mysqli_query($conn, $exercise_query);

if (!$exercise_result) {
    die("Query Failed: " . mysqli_error($conn));
}

$doctor_id = $_SESSION['user_id']; // Doctor's ID from session
$doctor_name = $_SESSION['user_name']; // Doctor's name from session

// Fetch Patient IDs for the doctor's appointments
$patient_query = "
    SELECT DISTINCT patient.PatientID, patient.Name 
    FROM appointments
    JOIN patient ON appointments.PatientID = patient.PatientID
    WHERE appointments.DoctorID = '$doctor_id'";
$patient_result = mysqli_query($conn, $patient_query);

if (!$patient_result) {
    die("Query Failed: " . mysqli_error($conn));
}

// Check for patient history request
if (isset($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];
    $history_query = "SELECT history FROM rheumatologist WHERE patientid = '$patient_id' ORDER BY treatmentid DESC LIMIT 1";
    $history_result = mysqli_query($conn, $history_query);

    if ($history_result && mysqli_num_rows($history_result) > 0) {
        $history_data = mysqli_fetch_assoc($history_result);
        echo json_encode(['history' => $history_data['history']]);
    } else {
        echo json_encode(['history' => 'No previous history available.']);
    }

    exit;
}
if (isset($_GET['patient']) && isset($_GET['PatientID'])) {
    $patient_id = $_GET['PatientID'];
    $details_query = "
        SELECT Name, Email, PhoneNumber, DateOfBirth, Gender 
        FROM patient 
        WHERE PatientID = '$patient_id'";
    $details_result = mysqli_query($conn, $details_query);

    if ($details_result && mysqli_num_rows($details_result) > 0) {
        $details_data = mysqli_fetch_assoc($details_result);
        echo json_encode($details_data);
    } else {
        echo json_encode(['error' => 'No patient details found.']);
    }

    exit;
}

// Handle form submission
if (isset($_POST['submit'])) {
    // Get the form data
    $patient_id = $_POST['patient_id'];
    $followup_date = $_POST['followup_date'];
    $exercises = $_POST['exercises'];

    // Loop through the exercises and insert each one into the physiotherapy table
    foreach ($exercises as $exercise_id) {
        // Get exercise details
        $exercise_query = "SELECT exercise FROM exercise_details WHERE e_id = '$exercise_id'";
        $exercise_result = mysqli_query($conn, $exercise_query);
        $exercise_data = mysqli_fetch_assoc($exercise_result);
        $exercise_name = $exercise_data['exercise'];

        // Get exercise instructions (assuming you have a separate table or predefined instructions for each exercise)
        $instruction_query = "SELECT instructions FROM exercise_details WHERE e_id = '$exercise_id'";
        $instruction_result = mysqli_query($conn, $instruction_query);
        $instruction_data = mysqli_fetch_assoc($instruction_result);
        $instructions = $instruction_data['instructions'];

        // Insert data into the physiotherapy table
        $insert_query = "
            INSERT INTO physiotherapy (DoctorID, PatientID, exercise, instruction, followup_date) 
            VALUES ('$doctor_id', '$patient_id', '$exercise_name', '$instructions', '$followup_date')
        ";

        $insert_result = mysqli_query($conn, $insert_query);

        if (!$insert_result) {
            die("Error inserting data: " . mysqli_error($conn));
        }
    }

    echo "Treatment details submitted successfully!";
    exit; // Redirect or display success message as needed
}
// Check for patient details request
if (isset($_GET['get_patient_details']) && isset($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];
    $details_query = "
        SELECT Name,  Email, PhoneNumber,DateOfBirth, Gender 
        FROM patient 
        WHERE PatientID = '$patient_id'";
    $details_result = mysqli_query($conn, $details_query);

    if ($details_result && mysqli_num_rows($details_result) > 0) {
        $details_data = mysqli_fetch_assoc($details_result);
        echo json_encode($details_data);
    } else {
        echo json_encode(['error' => 'No patient details found.']);
    }

    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            display: flex;
            justify-content: flex-start; /* Align form to the left */
            align-items: flex-start;
            height: 100vh;
            padding: 20px; /* Add padding to the body */
        }

        .form-container {
            width: 50%; /* Reduce width of the form */
            max-width: 600px; /* Limit maximum width */
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: left;
            color: #4CAF50;
            margin-bottom: 20px; /* Space between title and form */
        }

        .form-group {
            margin-top: 10px;
        }

        .form-control {
            padding: 5px;
            margin: 5px 0;
            width: 100%;
        }

        .btn {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .test-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .test-table th, .test-table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }

        .exercise-select {
            padding: 8px;
            font-size: 14px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-container, .patient-details-container {
            width: 47%;
            padding: 50px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        #history {
            font-size: 14px;
            color: #333;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            height: 150px;
            background-color: #f9f9f9;
            resize: none;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Treatment for Dr. <?php echo htmlspecialchars($doctor_name); ?></h2>
        <form action="" method="post">
            <!-- Patient ID Selection -->
            <div class="form-group">
                <label for="patient_id">Patient ID:</label>
                <input list="patient_list" id="patient_id" name="patient_id" required>
                <datalist id="patient_list">
                    <?php while ($patient = mysqli_fetch_assoc($patient_result)): ?>
                        <option value="<?php echo htmlspecialchars($patient['PatientID']); ?>">
                            <?php echo htmlspecialchars($patient['Name']); ?>
                        </option>
                    <?php endwhile; ?>
                </datalist>
            </div>

            <!-- Previous History -->
            <div class="form-group">
                <label for="history">Previous History:</label>
                <textarea id="history" class="form-control" readonly></textarea>
            </div>

            <!-- Exercise Details Grid -->
            <h3>Exercise Details</h3>
            <table class="test-table">
                <thead>
                    <tr>
                        <th>Exercise</th>
                        <th>Description</th>
                        <th>Instructions</th>
                    </tr>
                </thead>
                <tbody id="exercise-grid">
                    <tr>
                        <td>
                            <select class="form-control exercise-select" name="exercises[]">
                                <option value="">Select Exercise</option>
                                <?php while ($exercise = mysqli_fetch_assoc($exercise_result)): ?>
                                    <option value="<?php echo htmlspecialchars($exercise['e_id']); ?>">
                                        <?php echo htmlspecialchars($exercise['exercise']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </td>
                        <td><span class="exercise-description"></span></td>
                        <td><span class="exercise-instructions"></span></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn" onclick="addExerciseRow()">Add Another Exercise</button>

            <br><br>

            <!-- Follow-up Date -->
            <div class="form-group">
                <label for="followup_date">Follow-up Date:</label>
                <input type="date" id="followup_date" name="followup_date" class="form-control">
            </div>

            <button type="submit" name="submit" class="btn">Submit Treatment</button>
        </form>
    </div>
    <div class="patient-details-container">
        <h3>Patient Details</h3>
        <p><strong>Name:</strong> <span id="patient-name">-</span></p>
        <p><strong>Date of Birth:</strong> <span id="patient-dob">-</span></p>
        <p><strong>Email:</strong> <span id="patient-email">-</span></p>
        <p><strong>Phone:</strong> <span id="patient-phone">-</span></p>
        <p><strong>Gender:</strong> <span id="patient-gender">-</span></p>
    </div>                         
    <script>
        // Function to fetch history based on selected patient
        document.getElementById('patient_id').addEventListener('change', function() {
            const patientId = this.value;

            if (patientId) {
                fetch(`?patient_id=${patientId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('history').value = data.history;
                    })
                    .catch(error => {
                        console.error('Error fetching history:', error);
                    });
            }
        });

        // Add a new row in the exercise details grid (for adding exercises)
        function addExerciseRow() {
            let table = document.getElementById('exercise-grid');
            let newRow = table.insertRow();
            newRow.innerHTML = `
                <td>
                    <select class="form-control exercise-select" name="exercises[]">
                        <option value="">Select Exercise</option>
                        <?php
                            mysqli_data_seek($exercise_result, 0); // Reset the pointer to the start of the result
                            while ($exercise = mysqli_fetch_assoc($exercise_result)) { ?>
                                <option value="<?php echo htmlspecialchars($exercise['e_id']); ?>">
                                    <?php echo htmlspecialchars($exercise['exercise']); ?>
                                </option>
                        <?php } ?>
                    </select>
                </td>
                <td><span class="exercise-description"></span></td>
                <td><span class="exercise-instructions"></span></td>
            `;
            // Attach the event listener to the newly added select element
            const newSelect = newRow.querySelector('.exercise-select');
            newSelect.addEventListener('change', function() {
                const rowIndex = Array.from(newSelect.closest('tbody').children).indexOf(newSelect.closest('tr'));
                const exerciseId = newSelect.value;
                if (exerciseId) {
                    fetchExerciseDetails(exerciseId, rowIndex);
                }
            });
        }
        document.getElementById('patient_id').addEventListener('change', function () {
    const patientId = this.value;

    if (patientId) {
        fetch(`?patient=1&PatientID=${patientId}`) // Updated to match PHP condition
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('patient-name').textContent = data.error;
                    document.getElementById('patient-dob').textContent = '-';
                    document.getElementById('patient-email').textContent = '-';
                    document.getElementById('patient-phone').textContent = '-';
                    document.getElementById('patient-gender').textContent = '-';
                } else {
                    document.getElementById('patient-name').textContent = data.Name || 'N/A';
                    document.getElementById('patient-dob').textContent = data.DateOfBirth || 'N/A';
                    document.getElementById('patient-email').textContent = data.Email || 'N/A';
                    document.getElementById('patient-phone').textContent = data.PhoneNumber || 'N/A';
                    document.getElementById('patient-gender').textContent = data.Gender || 'N/A';
                }
            })
            .catch(error => {
                console.error('Error fetching patient details:', error);
            });
    }
});

        // Fetch exercise details using AJAX
        function fetchExerciseDetails(exerciseId, rowIndex) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get_exercise_details.php?id=${exerciseId}`, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);
                    const descSpan = document.querySelector(`#exercise-grid tr:nth-child(${rowIndex + 1}) .exercise-description`);
                    const instSpan = document.querySelector(`#exercise-grid tr:nth-child(${rowIndex + 1}) .exercise-instructions`);
                    descSpan.innerHTML = data.description || 'No description available.';
                    instSpan.innerHTML = data.instructions || 'No instructions available.';
                }
            };
            xhr.send();
        }

        // Listen to exercise selection changes for existing rows (initial load)
        document.querySelectorAll('.exercise-select').forEach(select => {
            select.addEventListener('change', function() {
                const rowIndex = Array.from(select.closest('tbody').children).indexOf(select.closest('tr'));
                const exerciseId = select.value;
                if (exerciseId) {
                    fetchExerciseDetails(exerciseId, rowIndex);
                }
            });
        });
    </script>
</body>
</html>
