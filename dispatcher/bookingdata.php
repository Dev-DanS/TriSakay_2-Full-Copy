<?php
// Include the database connection file
include('../db/dbconn.php');

// Query to select data from the "booking" table and join with the "commuters" table
$sql = "SELECT b.bookingid, b.toda, b.commuterid, c.firstname, b.passengercount, b.fare, b.conveniencefee, b.Distance FROM booking b
        LEFT JOIN commuter c ON b.commuterid = c.commuterid";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data from each row
    while ($row = $result->fetch_assoc()) {
        echo "<div class='booking-info'>";
        echo "<p>Booking ID: " . $row["bookingid"] . "</p>";
        echo "<p>Commuter: " . $row["firstname"] . "</p>";
        echo "<p>Passenger Count: " . $row["passengercount"] . "</p>";
        echo "<p>Fare: ₱" . $row["fare"] . "</p>";
        echo "<p>Convenience Fee: ₱" . $row["conveniencefee"] . "</p>";
        echo "<p>Distance: " . number_format($row["Distance"], 3) . " km</p>";
        echo "<a href='leaflet_map.php?bookingid=" . $row["bookingid"] . "'>Accept</a>";
        echo "</div>";
    }
} else {
    echo "No data found.";
}

// Close the database connection
$conn->close();
?>