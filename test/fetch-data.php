<?php
include("../db/dbconn.php");

$sql = "SELECT borders, toda FROM route";
$result = mysqli_query($conn, $sql);

$borders = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $entry = [
            'borders' => json_decode($row["borders"], true),
            'toda' => $row['toda']
        ];
        $borders[] = $entry;
    }
}
echo json_encode($borders);

mysqli_close($conn);
?>