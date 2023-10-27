<?php
include('../php/session_commuter.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>On going | Commuter</title>
    <?php
    $imagePath = "../img/Logo_Nobg.png";
    ?>
    <link rel="icon" href="<?php echo $imagePath; ?>" type="image/png" />
    <?php
    include '../dependencies/dependencies.php';
    ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <link rel="stylesheet" href="../css/ongoing.css">
</head>

<body>
    <?php
    include('../php/navbar_commuter.php');
    ?>
    <div id="map" style="height: 400px;"></div>


    <script>
        var currentPoint = null; // Initialize current point variable
        var marker = null; // Initialize marker variable

        // Initialize the map
        var map = L.map('map', {
            zoomControl: false,      // Disable the default zoom control
            doubleClickZoom: false,  // Disable double-click zoom
        }).setView([0, 0], 16); // Default center and zoom level

        // Add a tile layer to the map
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Function to update the map and marker
        function updateMapAndMarker(position) {
            var lat = position.coords.latitude;
            var lon = position.coords.longitude;
            currentPoint = [lat, lon]; // Update current point

            // Center the map on the user's current position
            map.setView(currentPoint, 16);

            // Remove the previous marker if it exists
            if (marker) {
                map.removeLayer(marker);
            }

            // Create a marker for the current location
            marker = L.marker(currentPoint).addTo(map);

            // Add a popup to the marker
            marker.bindPopup("You are here").openPopup();
        }

        // Use HTML5 Geolocation's watchPosition method for live updates
        if ("geolocation" in navigator) {
            navigator.geolocation.watchPosition(updateMapAndMarker);
        }
        
    </script>




</body>

</html>