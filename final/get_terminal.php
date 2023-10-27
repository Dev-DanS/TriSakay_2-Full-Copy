<?php
include '../db/dbconn.php';

$toda = $_GET['term'];
$query = "SELECT terminal FROM todalocation WHERE toda = '$toda'";
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'No terminal found for this toda.']);
}
?>