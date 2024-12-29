<?php 
include("Database.php");

// Fetch the list of doctors from the database
$doctor_query = "SELECT DoctorID, name FROM doctor";
$doctor_result = mysqli_query($conn, $doctor_query);

// Check if a doctor is selected and fetch treatment history
if (isset($_GET['doctor_id'])) {
    $doctor_id = $_GET['doctor_id'];

    // Fetch treatment history from the rheumatologist table based on DoctorID
    $query = "SELECT * FROM rheumatologist WHERE DoctorID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $doctor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Output the treatment history or a message if no history is found
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<p><strong>Treatment ID:</strong> " . htmlspecialchars($row['treatmentid']) . "</p>";
            echo "<p><strong>Disease:</strong> " . htmlspecialchars($row['disease']) . "</p>";
            echo "<p><strong>History:</strong> " . htmlspecialchars($row['history']) . "</p>";
            echo "<p><strong>Symptom:</strong> " . htmlspecialchars($row['symptom']) . "</p>";
            echo "<p><strong>Test Report:</strong> " . htmlspecialchars($row['test_report']) . "</p>";
            echo "<p><strong>Follow-up Date:</strong> " . htmlspecialchars($row['followup_date']) . "</p>";
            echo "<p><strong>Medicine:</strong> " . htmlspecialchars($row['medicine']) . "</p>";
            echo "<p><strong>Dosage:</strong> " . htmlspecialchars($row['dosage']) . "</p>";
            echo "<p><strong>Medicine Instructions:</strong> " . htmlspecialchars($row['m_instruction']) . "</p>";
            echo "<p><strong>Test:</strong> " . htmlspecialchars($row['test']) . "</p>";
            echo "<p><strong>Test Instructions:</strong> " . htmlspecialchars($row['t_instructions']) . "</p>";
        }
    } else {
        echo "<p>No treatment history found for the selected doctor.</p>";
    }
    exit; // Stop further processing since the response has already been sent
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Treatment</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .form-container {
            width: 50%;
            max-width: 600px;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: auto;
        }

        h2 {
            text-align: left;
            color: #4CAF50;
            margin-bottom: 20px;
        }

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

        .history-container {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Treatment</h2>
    
    <!-- Doctor Selection Dropdown -->
    <div class="form-group">
        <label for="doctor_id">Select Doctor:</label>
        <select id="doctor_id" name="doctor_id" class="form-control" required>
            <option value="" disabled selected>Select a Doctor</option>
            <?php while ($doctor = mysqli_fetch_assoc($doctor_result)): ?>
                <option value="<?php echo htmlspecialchars($doctor['DoctorID']); ?>">
                    <?php echo htmlspecialchars($doctor['name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <!-- Treatment History Container -->
    <div class="history-container" id="history-container" style="display:none;">
        <h4>Patient Treatment History</h4>
        <div id="history-details"></div>
    </div>

</div>

<script>
    $(document).ready(function() {
        $('#doctor_id').change(function() {
            var doctorID = $(this).val();

            if (doctorID) {
                $.ajax({
                    type: 'GET',
                    url: '', // This refers to the same PHP file
                    data: { doctor_id: doctorID },
                    success: function(response) {
                        $('#history-container').show();
                        $('#history-details').html(response);
                    }
                });
            } else {
                $('#history-container').hide();
            }
        });
    });
</script>

</body>
</html>
