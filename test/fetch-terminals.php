<?php
include("../db/dbconn.php");

$toda = $_GET['toda'];
$sql = "SELECT terminal FROM todalocation WHERE toda = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $toda);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['terminal' => $row['terminal']]);
} else {
    echo json_encode(['terminal' => null]);
}

mysqli_close($conn);
?>