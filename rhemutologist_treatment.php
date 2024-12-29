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

// Fetch distinct diseases and symptoms from the treatment table
$diseases_query = "SELECT DISTINCT diseases FROM treatment";
$diseases_result = mysqli_query($conn, $diseases_query);

$symptoms_query = "SELECT DISTINCT symptom FROM treatment";
$symptoms_result = mysqli_query($conn, $symptoms_query);

// Fetch medicines details for the dropdown
$medicines_query = "SELECT DISTINCT medicine FROM treatment";
$medicines_result = mysqli_query($conn, $medicines_query);

$dosage_query = "SELECT DISTINCT m_dosage FROM treatment";
$instructions_query = "SELECT DISTINCT LEFT(m_instructions, 100) AS m_instructions FROM treatment"; // Limit to 100 chars

$dosage_result = mysqli_query($conn, $dosage_query);
$instructions_result = mysqli_query($conn, $instructions_query);

// Fetch lab tests
// Update the tests query to include test name
$tests_query = "SELECT LabID, Name, instructions FROM lab";
$tests_result = mysqli_query($conn, $tests_query);


if (!$diseases_result || !$symptoms_result || !$medicines_result || !$dosage_result || !$instructions_result || !$tests_result) {
    die("Query Failed: " . mysqli_error($conn));
}
if (isset($_POST['submit'])) {
    $patient_id = $_POST['patient_id'];
    $bp = $_POST['bp'] ?? null;
    $sugar = $_POST['sugar'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $diseases = isset($_POST['diseases']) ? implode(",", $_POST['diseases']) : '';
    $symptoms = isset($_POST['symptoms']) ? implode(",", $_POST['symptoms']) : '';
    $medicines = isset($_POST['medicines']) ? implode(",", $_POST['medicines']) : '';
    $dosages = isset($_POST['dosage']) ? implode(",", $_POST['dosage']) : '';
    $instructions = isset($_POST['instructions']) ? implode(",", $_POST['instructions']) : '';
    $tests = isset($_POST['tests']) ? implode(",", $_POST['tests']) : '';
    $test_instructions = isset($_POST['test_instructions']) ? implode(",", $_POST['test_instructions']) : '';
    $followup_date = $_POST['followup_date'] ?? null;  // Get follow-up date

    // Fetch existing history from the rheumatologist table
    $history_query = "SELECT history FROM rheumatologist WHERE patientid = '$patient_id' ORDER BY treatmentid DESC LIMIT 1";
    $history_result = mysqli_query($conn, $history_query);
    $existing_history = ($history_result && mysqli_num_rows($history_result) > 0) 
        ? mysqli_fetch_assoc($history_result)['history'] 
        : 'NULL';

    // Concatenate the new history
    $new_history = "BP: $bp, Sugar: $sugar, Weight: $weight, Diseases: $diseases, Symptoms: $symptoms, Medicines: $medicines, Dosages: $dosages, Instructions: $instructions, Tests: $tests, Test Instructions: $test_instructions";
    $complete_history = $existing_history !== 'NULL' ? $existing_history . " | " . $new_history : $new_history;

    // Insert into rheumatologist table
    $insert_query = "
        INSERT INTO rheumatologist (doctorid, patientid, disease, history, symptom, test_report, followup_date, medicine, dosage, m_instruction, test, t_instructions) 
        VALUES ('$doctor_id', '$patient_id', '$diseases', '$complete_history', '$symptoms', '', '$followup_date', '$medicines', '$dosages', '$instructions', '$tests', '$test_instructions')";
    
    if (mysqli_query($conn, $insert_query)) {
        echo "Treatment saved successfully.";
    } else {
        die("Error saving treatment: " . mysqli_error($conn));
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


if (isset($_POST['get_tests'])) {
    $tests_query = "SELECT  Name FROM lab_tests";
    $tests_result = mysqli_query($conn, $tests_query);

    if ($tests_result) {
        $tests = [];
        while ($test = mysqli_fetch_assoc($tests_result)) {
            $tests[] = $test;
        }
        echo json_encode($tests);
    } else {
        echo json_encode([]);
    }
    exit;
}

if (isset($_POST['test'])) {
    $test_id = $_POST['test'];
    $test_query = "SELECT instructions FROM lab WHERE LabID = '$test_id' LIMIT 1";
    $test_result = mysqli_query($conn, $test_query);

    if ($test_result && mysqli_num_rows($test_result) > 0) {
        $test_data = mysqli_fetch_assoc($test_result);
        echo json_encode(['instructions' => $test_data['instructions']]); // Return instructions as JSON
    } else {
        echo json_encode(['instructions' => '']);
    }
    exit;
}
if (isset($_POST['submit'])) {
    $patient_id = $_POST['patient_id'];
    $bp = $_POST['bp'] ?? null;
    $sugar = $_POST['sugar'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $diseases = isset($_POST['diseases']) ? implode(",", $_POST['diseases']) : '';
    $symptoms = isset($_POST['symptoms']) ? implode(",", $_POST['symptoms']) : '';
    $medicines = isset($_POST['medicines']) ? implode(",", $_POST['medicines']) : '';
    $dosages = isset($_POST['dosage']) ? implode(",", $_POST['dosage']) : '';
    $instructions = isset($_POST['instructions']) ? implode(",", $_POST['instructions']) : '';
    $tests = isset($_POST['tests']) ? implode(",", $_POST['tests']) : '';
    $test_instructions = isset($_POST['test_instructions']) ? implode(",", $_POST['test_instructions']) : '';

    // Fetch existing history from the rheumatologist table
    $history_query = "SELECT history FROM rheumatologist WHERE patientid = '$patient_id' ORDER BY treatmentid DESC LIMIT 1";
    $history_result = mysqli_query($conn, $history_query);
    $existing_history = ($history_result && mysqli_num_rows($history_result) > 0) 
        ? mysqli_fetch_assoc($history_result)['history'] 
        : 'NULL';

    // Concatenate the new history
    $new_history = "BP: $bp, Sugar: $sugar, Weight: $weight, Diseases: $diseases, Symptoms: $symptoms, Medicines: $medicines, Dosages: $dosages, Instructions: $instructions, Tests: $tests, Test Instructions: $test_instructions";
    $complete_history = $existing_history !== 'NULL' ? $existing_history . " | " . $new_history : $new_history;

    // Insert into rheumatologist table
    $insert_query = "
        INSERT INTO rheumatologist (doctorid, patientid, disease, history, symptom, test_report, followup_date, medicine, dosage, m_instruction, test, t_instructions) 
        VALUES ('$doctor_id', '$patient_id', '$diseases', '$complete_history', '$symptoms', '', NULL, '$medicines', '$dosages', '$instructions', '$tests', '$test_instructions')";
    if (mysqli_query($conn, $insert_query)) {
        echo "Treatment saved successfully.";
    } else {
        die("Error saving treatment: " . mysqli_error($conn));
    }
    exit;
}
if (isset($_POST['fetch_history'])) {
    $patient_id = $_POST['patient_id'];
    $history_query = "SELECT history FROM rheumatologist WHERE patientid = '$patient_id' ORDER BY treatmentid DESC LIMIT 1";
    $history_result = mysqli_query($conn, $history_query);
    if ($history_result && mysqli_num_rows($history_result) > 0) {
        echo mysqli_fetch_assoc($history_result)['history'];
    } else {
        echo 'No previous history available.';
    }
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        
        /* General Styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 20px;
    display: flex;
    justify-content: flex-start; /* Align form to the left */
    align-items: flex-start;
    height: 100vh;
}

/* Form Container */
.form-container {
    width: 50%; /* Adjust width */
    max-width: 600px; /* Limit maximum width */
    padding: 20px;
    background-color: #ffffff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-right: 20px;
}

/* Patient Details Container */
.patient-details-container {
    width: 47%;
    padding: 20px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Header Styling */
h2 {
    text-align: left;
    color: #4CAF50;
    margin-bottom: 20px; /* Space between title and form */
}

h3 {
    color: #007BFF;
    margin-top: 20px;
}

/* Form Group Styling */
.form-group {
    margin-top: 10px;
}

.form-control {
    padding: 10px;
    margin: 5px 0;
    width: 100%;
    border-radius: 5px;
    border: 1px solid #ccc;
}

textarea {
    padding: 10px;
    border-radius: 5px;
    width: 100%;
    height: 150px;
    border: 1px solid #ccc;
}

/* Button Styling */
.btn {
    padding: 10px 20px;
    background-color: #007BFF;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-top: 10px;
}

.btn:hover {
    background-color: #0056b3;
}

/* Dropdown Button */
.custom-dropdown {
    position: relative;
    display: inline-block;
    width: 100%;
}

.dropdown-btn {
    display: block;
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #f9f9f9;
    cursor: pointer;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    width: 100%;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1;
    max-height: 200px;
    overflow-y: auto;
}

.dropdown-content label {
    padding: 8px;
    display: block;
}

.dropdown-btn:focus + .dropdown-content,
.dropdown-content:hover {
    display: block;
}

/* Table Styling */
.medicine-table, .test-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.medicine-table th, .test-table th, .medicine-table td, .test-table td {
    padding: 10px;
    text-align: center;
    border: 1px solid #ddd;
}

.medicine-table th, .test-table th {
    background-color: #f8f9fa;
}

.test-table td {
    position: relative;
}

.test-instructions {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
}

/* Add Row Button */
.btn {
    padding: 10px 20px;
    background-color: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-top: 10px;
}

.btn:hover {
    background-color: #218838;
}

/* Follow-up Date Styling */
#followup_date {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 100%;
    margin-top: 10px;
}

.patient-details-container p {
    font-size: 14px;
    margin-bottom: 10px;
}

/* Responsive Styling */
@media screen and (max-width: 768px) {
    .form-container, .patient-details-container {
        width: 100%;
        margin-bottom: 20px;
    }
}

    </style>
</head>
<body>
    <h2>Treatment for <?php echo htmlspecialchars($doctor_name); ?></h2>

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
        <div class="form-group">
    <label for="bp">Blood Pressure (BP):</label>
    <input type="text" id="bp" name="bp" class="form-control" placeholder="Enter Blood Pressure (e.g., 120/80)" list="bp_suggestions">
    <datalist id="bp_suggestions">
        <option value="120/80">
        <option value="130/85">
        <option value="140/90">
        <option value="110/70">
        <option value="125/80">
    </datalist>
</div>

<div class="form-group">
    <label for="sugar">Blood Sugar:</label>
    <input type="text" id="sugar" name="sugar" class="form-control" placeholder="Enter Blood Sugar (e.g., 90)" list="sugar_suggestions">
    <datalist id="sugar_suggestions">
        <option value="90">
        <option value="110">
        <option value="130">
        <option value="100">
        <option value="120">
    </datalist>
</div>

<div class="form-group">
    <label for="weight">Weight:</label>
    <input type="text" id="weight" name="weight" class="form-control" placeholder="Enter Weight (e.g., 70)" list="weight_suggestions">
    <datalist id="weight_suggestions">
        <option value="70">
        <option value="75">
        <option value="80">
        <option value="85">
        <option value="90">
    </datalist>

</div>
<div class="form-group">
    <label for="history">Previous History:</label>
    <textarea id="history" class="form-control" readonly>
        <?php
        if (isset($_POST['patient_id'])) {
            $patient_id = $_POST['patient_id'];
            $history_query = "SELECT history FROM rheumatologist WHERE patientid = '$patient_id' ORDER BY treatmentid DESC LIMIT 1";
            $history_result = mysqli_query($conn, $history_query);
            echo ($history_result && mysqli_num_rows($history_result) > 0) 
                ? htmlspecialchars(mysqli_fetch_assoc($history_result)['history']) 
                : 'No previous history available.';
        }
        ?>
    </textarea>
</div>


        <!-- Diseases Dropdown with Checkboxes -->
        <div class="form-group custom-dropdown">
            <label for="diseases"></label>
            <div class="dropdown-btn" tabindex="0">Select Diseases</div>
            <div class="dropdown-content">
                <?php while ($disease = mysqli_fetch_assoc($diseases_result)): ?>
                    <label>
                        <input type="checkbox" name="diseases[]" value="<?php echo htmlspecialchars($disease['diseases']); ?>">
                        <?php echo htmlspecialchars($disease['diseases']); ?>
                    </label>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Symptoms Dropdown with Checkboxes -->
        <div class="form-group custom-dropdown">
            <label for="symptoms"></label>
            <div class="dropdown-btn" tabindex="0">Select Symptoms</div>
            <div class="dropdown-content">
                <?php while ($symptom = mysqli_fetch_assoc($symptoms_result)): ?>
                    <label>
                        <input type="checkbox" name="symptoms[]" value="<?php echo htmlspecialchars($symptom['symptom']); ?>">
                        <?php echo htmlspecialchars($symptom['symptom']); ?>
                    </label>
                <?php endwhile; ?>
            </div>
        </div>
                    <!-- BP, Sugar, Weight Textboxes with Suggestions -->

        <!-- Medicine Details Grid -->
        <h3>Medicine Details</h3>
        <table class="medicine-table">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Dosage</th>
                    <th>Instructions</th>
                </tr>
            </thead>
            <tbody id="medicine-grid">
                <tr>
                    <td>
                        <select class="form-control medicine-select" name="medicines[]">
                            <option value="">Select Medicine</option>
                            <?php while ($medicine = mysqli_fetch_assoc($medicines_result)): ?>
                                <option value="<?php echo htmlspecialchars($medicine['medicine']); ?>">
                                    <?php echo htmlspecialchars($medicine['medicine']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                    <td>
                        <select class="form-control dosage-select" name="dosage[]">
                            <option value="">Select Dosage</option>
                            <?php while ($dosage = mysqli_fetch_assoc($dosage_result)): ?>
                                <option value="<?php echo htmlspecialchars($dosage['m_dosage']); ?>">
                                    <?php echo htmlspecialchars($dosage['m_dosage']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                    <td>
                        <select class="form-control instructions-select" name="instructions[]">
                            <option value="">Select Instructions</option>
                            <?php while ($instruction = mysqli_fetch_assoc($instructions_result)): ?>
                                <option value="<?php echo htmlspecialchars($instruction['m_instructions']); ?>">
                                    <?php echo htmlspecialchars($instruction['m_instructions']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn" onclick="addMedicineRow()">Add Another Medicine</button>

        <!-- Test Details Grid -->
        <h3>Test Details</h3>
        <table class="test-table">
            <thead>
                <tr>
                    <th>Test</th>
                    <th>Instructions</th>
                </tr>
            </thead>
            <tbody id="test-grid">
                <tr>
                    <td>
                        <select class="form-control test-select" name="tests[]">
                            <option value="">Select Test</option>
                            <?php while ($test = mysqli_fetch_assoc($tests_result)): ?>
                                <option value="<?php echo htmlspecialchars($test['LabID']); ?>">
                                    <?php echo htmlspecialchars($test['Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                    <td>
                        <span class="test-instructions"></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn" onclick="addTestRow()">Add Another Test</button>
        <div class="form-group">
    <label for="followup_date">Follow-up Date:</label>
    <input type="date" id="followup_date" name="followup_date" class="form-control">
</div>
                  
        <br><br>
        <button type="submit" name="submit" class="btn">Submit Treatment</button>
    </form>
    <div class="patient-details-container">
        <h3>Patient Details</h3>
        <p><strong>Name:</strong> <span id="patient-name">-</span></p>
        <p><strong>Date of Birth:</strong> <span id="patient-dob">-</span></p>
        <p><strong>Email:</strong> <span id="patient-email">-</span></p>
        <p><strong>Phone:</strong> <span id="patient-phone">-</span></p>
        <p><strong>Gender:</strong> <span id="patient-gender">-</span></p>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Add a new medicine row
        function addMedicineRow() {
            var row = $('#medicine-grid tr:first').clone();
            row.find('select').val('');
            $('#medicine-grid').append(row);
        }

        // Add a new test row
        function addTestRow() {
            var row = $('#test-grid tr:first').clone();
            row.find('.test-instructions').text('');
            $('#test-grid').append(row);
        }
        $(document).on('change', '#patient_id', function () {
    var patientId = $(this).val();
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

    if (patientId) {
        $.ajax({
            url: '',
            type: 'POST',
            data: { fetch_history: true, patient_id: patientId },
            success: function (response) {
                $('#history').val(response);
            }
        });
    } else {
        $('#history').val('No previous history available.');
    }
});

       // Fetch test instructions when test is selected
$(document).on('change', '.test-select', function() {
    var testId = $(this).val();
    var row = $(this).closest('tr');

    if (testId) {
        $.ajax({
            url: '',
            type: 'POST',
            data: { test: testId },
            success: function(response) {
                var data = JSON.parse(response);
                row.find('.test-instructions').text(data.instructions); // Display test instructions
            }
        });
    } else {
        row.find('.test-instructions').text('');
    }
});

    </script>
</body>
</html>
