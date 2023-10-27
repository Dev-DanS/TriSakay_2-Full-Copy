<?php
// Include the database connection file (e.g., dbconn.php)
include('../db/dbconn.php');

if (isset($_GET['bookingid'])) {
    $bookingId = $_GET['bookingid'];

    // Query the database to retrieve the pickup and drop-off coordinates
    // Replace with your database query
    // Assume you have a table named "bookings" with columns "pickuppoint" and "dropoffpoint"
    $query = "SELECT pickuppoint, dropoffpoint, toda FROM booking WHERE bookingid = $bookingId";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pickupPoint = explode(',', $row['pickuppoint']);
        $dropoffPoint = explode(',', $row['dropoffpoint']);
        $toda = $row['toda'];

        // Query the "todalocation" table to get the terminal based on "toda"
        $query = "SELECT terminal FROM todalocation WHERE toda = '$toda'";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $terminal = json_decode($row['terminal'], true);

            // Return coordinates and terminal as JSON
            $response = [
                'pickupPoint' => $pickupPoint,
                'dropoffPoint' => $dropoffPoint,
                'terminal' => $terminal,
            ];

            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'Terminal not found for the provided TODA']);
        }
    } else {
        echo json_encode(['error' => 'Booking not found']);
    }
} else {
    echo json_encode(['error' => 'Booking ID not provided']);
}
?>