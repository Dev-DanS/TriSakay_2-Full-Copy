<?php
include('../db/dbconn.php');
$query = "SELECT * FROM route LIMIT 1";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$toda = $row['toda'];
$borders = json_decode($row['borders'], true);
?>