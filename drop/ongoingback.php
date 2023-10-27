<?php
// Include the database connection
require_once '../db/dbconn.php';

// Get the commuter ID from the session
$commuterid = $_SESSION["commuterid"];

// SQL query to select the latest booking data
$sql = "SELECT PickupPoint, DropoffPoint
        FROM booking
        WHERE status = 'onprogress' AND CommuterID = ?
        ORDER BY bookingdate DESC
        LIMIT 1";

// Prepare the SQL statement
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Bind the parameter
    $stmt->bind_param("i", $commuterid);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Return data as JSON
        echo json_encode($row);
    } else {
        echo json_encode(array()); // Return an empty JSON object if no data found
    }

    // Close the prepared statement
    $stmt->close();
} else {
    echo "Error in SQL statement";
}

// Close the database connection
$conn->close();
?>