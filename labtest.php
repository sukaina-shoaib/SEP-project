<?php
session_start();
include("Database.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$patient_id = $_SESSION['user_id']; // Get the logged-in patient's ID

$labtest_details = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["test_types"])) {
    $test_types = $_POST["test_types"]; // Array of selected test types
    $test_date = $_POST["test_date"];
    $test_time = $_POST["test_time"];

    // Initialize arrays to hold selected tests and their costs
    $selected_tests = [];
    $total_cost = 0;
    $lab_ids = [];

    // Fetch descriptions and details for the selected tests
    foreach ($test_types as $test) {
        $test_query = "SELECT LabID, Name, description, Amount FROM Lab WHERE Name = '$test'";
        $result = mysqli_query($conn, $test_query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $selected_tests[] = [
                "name" => $row['Name'],
                "description" => $row['description'] ?? "No description available.",
                "amount" => $row['Amount']
            ];
            $lab_ids[] = $row['LabID']; // Store LabID for insertion
            $total_cost += $row['Amount']; // Add test cost to total
        }
    }

    // Insert each selected test into the LabTest table separately with unique testno
    foreach ($lab_ids as $lab_id) {
        $testno = "T" . time() . rand(1000, 9999) . $lab_id; // Unique testno for each test
        $labtest_query = "INSERT INTO LabTest (testno, PatientID, Date, Time, Name, LabID) 
                          VALUES ('$testno', '$patient_id', '$test_date', '$test_time', 
                                  '" . implode(", ", $test_types) . "', '$lab_id')";
        if (!mysqli_query($conn, $labtest_query)) {
            echo "Error: " . mysqli_error($conn);
            exit;
        }
    }

    // Save the details for display
    $labtest_details = [
        "receipt_no" => $testno,
        "tests" => $selected_tests,
        "date" => $test_date,
        "time" => $test_time,
        "total_cost" => $total_cost
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Lab Test</title>
    <style>
        .form-wrapper, .labtest-details { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ccc; }
        h2 { text-align: center; }
        input, button, select { width: 100%; padding: 10px; margin: 10px 0; }
        button { background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        .details { margin: 10px 0; }
        .test-options { display: flex; flex-direction: column; margin: 10px 0; }
        .test-option { margin-bottom: 10px; }
    </style>
</head>
<body>
    <?php if ($labtest_details): ?>
        <div class="labtest-details">
            <h2>Your Lab Test Details</h2>
            <div class="details"><strong>Test Number:</strong> <?php echo $labtest_details['receipt_no']; ?></div>
            <div class="details"><strong>Date:</strong> <?php echo $labtest_details['date']; ?></div>
            <div class="details"><strong>Time:</strong> <?php echo $labtest_details['time']; ?></div>
            <div class="details"><strong>Selected Tests:</strong></div>
            <ul>
                <?php foreach ($labtest_details['tests'] as $test): ?>
                    <li><strong><?php echo $test['name']; ?>:</strong> <?php echo $test['description']; ?> - $<?php echo $test['amount']; ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="details"><strong>Total Cost:</strong> $<?php echo $labtest_details['total_cost']; ?></div>
            <a href="index.php">Return to Main Page</a>
        </div>
    <?php else: ?>
        <div class="form-wrapper">
            <h2>Book a Lab Test</h2>
            <form method="POST" action="">
                <div class="test-options">
                    <div class="test-option">
                        <input type="checkbox" id="xray" name="test_types[]" value="X-ray">
                        <label for="xray">X-ray - A quick and painless imaging test.</label>
                    </div>
                    <div class="test-option">
                        <input type="checkbox" id="ctscan" name="test_types[]" value="CT Scan">
                        <label for="ctscan">CT Scan - Detailed body imaging using X-rays.</label>
                    </div>
                    <div class="test-option">
                        <input type="checkbox" id="ultrasound" name="test_types[]" value="Ultrasound">
                        <label for="ultrasound">Ultrasound - Imaging with sound waves.</label>
                    </div>
                    <div class="test-option">
                        <input type="checkbox" id="injection" name="test_types[]" value="Injection">
                        <label for="injection">Injection - Direct medicine administration.</label>
                    </div>
                </div>
                <label for="test_date">Select Date</label>
                <input type="date" id="test_date" name="test_date" required>
                <label for="test_time">Select Time</label>
                <input type="time" id="test_time" name="test_time" required>
                <button type="submit">Book Test</button>
            </form>
        </div>
    <?php endif; ?>
</body>
</html>
