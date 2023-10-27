<?php
include '../db/dbconn.php';

$data = json_decode(file_get_contents('php://input'), true);
$lat = $data['lat'];
$lng = $data['lng'];

$pickup = "$lat,$lng"; // Coordinates as a string

$query = "INSERT INTO booking SET pickup = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $pickup);

if (mysqli_stmt_execute($stmt)) {
    echo "Coordinates saved successfully.";
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>