<?php
include('../php/session_commuter.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map with Current Location</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <?php
    include '../dependencies/dependencies.php';
    ?>
    <?php
    $imagePath = "../img/Logo_Nobg.png";
    ?>
    <link rel="icon" href="<?php echo $imagePath; ?>" type="image/png" />
</head>

<body>
    <?php
    include('../php/navbar_commuter.php');
    ?>
    <div id="map" style="width: 100%; height: 400px;"></div>

    <script>
        var map = L.map('map').setView([0, 0], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        // Get the user's current location and add a marker
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function (position) {
                var lat = position.coords.latitude;
                var lon = position.coords.longitude;

                // Set the map's center to the user's location
                map.setView([lat, lon], 16);

                // Add a marker with a custom message
                L.marker([lat, lon]).addTo(map)
                    .bindPopup("You are here").openPopup();
            });
        } else {
            alert("Geolocation is not available in your browser.");
        }
    </script>
</body>

</html>
