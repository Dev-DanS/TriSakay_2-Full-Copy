<?php
include '../db/dbconn.php';

// Check if the QR code result is provided in the URL
if (isset($_GET['qr_result'])) {
    $qrResult = $_GET['qr_result'];

    // Query the database to get the latest accepted booking
    $query = "SELECT commuterid, platenumber FROM booking WHERE status = 'accepted' ORDER BY bookingdate DESC LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        $platenumber = $row['platenumber'];

        // Compare the QR code result to the platenumber from the database
        if ($qrResult === $platenumber) {
            // Redirect to ongoing.php if there's a match
            header('Location: ongoing.php');
            exit();
        } else {
            // Handle the case where QR code doesn't match platenumber
            echo "QR code does not match any platenumber in the database.";
        }
    } else {
        // Handle the case where there are no accepted bookings in the database
        echo "No accepted bookings found in the database.";
    }
} else {
    // Handle the case where the QR result is not provided in the URL
    echo "QR result is missing.";
}
?>