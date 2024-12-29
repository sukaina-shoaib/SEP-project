<?php
include("Database.php");

if (isset($_GET['id'])) {
    $exercise_id = $_GET['id'];

    // Query to fetch the exercise details
    $query = "SELECT descriptions, instructions FROM exercise_details WHERE e_id = '$exercise_id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $exercise = mysqli_fetch_assoc($result);
        echo json_encode([
            'description' => $exercise['descriptions'],
            'instructions' => $exercise['instructions']
        ]);
    } else {
        echo json_encode([
            'description' => '',
            'instructions' => ''
        ]);
    }
} else {
    echo json_encode([
        'description' => '',
        'instructions' => ''
    ]);
}
?>
