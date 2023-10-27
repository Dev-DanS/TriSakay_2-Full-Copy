<?php
include '../db/dbconn.php'; // Include your connection details

$query = "SELECT BaseFare, PerKM, NightDiff, FarePerPassenger FROM farematrix WHERE status = 'active'";
$result = mysqli_query($conn, $query);
$fareData = mysqli_fetch_assoc($result);

echo json_encode($fareData);

?>