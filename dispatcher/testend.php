<?php
// Include your database connection code here (e.g., dbconn.php).
include('../db/dbconn.php');

// Get the booking ID from the URL.
$bookingId = $_GET['bookingid'];

// Query the database to get the pickup and dropoff points.
$query = "SELECT pickuppoint, dropoffpoint FROM booking WHERE bookingid = $bookingId";

// Execute the query and fetch the results.
$result = mysqli_query($db_connection, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);

    // Create an array to store the pickup and dropoff points.
    $coordinates = array(
        'pickup' => $row['pickuppoint'],
        'dropoff' => $row['dropoffpoint']
    );

    // Return the coordinates as JSON.
    echo json_encode($coordinates);
} else {
    // Handle the case where the query fails.
    echo json_encode(array('error' => 'Query failed'));
}

// Close the database connection.
mysqli_close($db_connection);
?>