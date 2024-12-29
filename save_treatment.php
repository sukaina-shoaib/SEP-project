<?php
include("Database.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $diseases = isset($_POST['diseases']) ? $_POST['diseases'] : [];
    $symptoms = isset($_POST['symptoms']) ? $_POST['symptoms'] : [];

    // Convert diseases and symptoms arrays to comma-separated strings
    $diseases_str = implode(", ", $diseases);
    $symptoms_str = implode(", ", $symptoms);

    // Save treatment details to the database
    $query = "INSERT INTO rheumatologist (PatientID, Diseases, Symptoms) VALUES ('$patient_id', '$diseases_str', '$symptoms_str')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "Treatment saved successfully.";
    } else {
        echo "Error saving treatment: " . mysqli_error($conn);
    }
}
?>
