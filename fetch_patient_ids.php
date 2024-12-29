<?php
include("database.php");

$term = $_GET['term'] ?? '';
$suggestions = [];

if (!empty($term)) {
    $query = "SELECT PatientID FROM Patient WHERE PatientID LIKE '%$term%'";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $suggestions[] = $row['PatientID'];
    }
}

echo json_encode($suggestions);
