<?php
include("database.php");

if (isset($_GET['term'])) {
    $term = $_GET['term'];

    // Fetch matching DoctorIDs from the database
    $query = "SELECT DoctorID FROM Doctor WHERE DoctorID LIKE '$term%' LIMIT 10";
    $result = mysqli_query($conn, $query);

    $ids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = $row['DoctorID'];
    }

    // Return IDs as a JSON response
    echo json_encode($ids);
}
?>
