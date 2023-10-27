<!DOCTYPE html>
<html>

<head>
    <title>Leaflet Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</head>

<body>
    <div id="map" style="height: 400px; width: 100%;"></div>

    <script>
        var map = L.map('map').setView([0, 0], 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        <?php
        include '../db/dbconn.php';

        $sql = "SELECT pickup FROM booking WHERE bookingid = 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $pickup = $row['pickup'];
                $coordinates = explode(",", $pickup);
                $lat = $coordinates[0];
                $lng = $coordinates[1];
                ?>

                var marker = L.marker([<?php echo $lat; ?>, <?php echo $lng; ?>]).addTo(map);
                map.setView([<?php echo $lat; ?>, <?php echo $lng; ?>], 15);

                <?php
            }
        }
        $conn->close();
        ?>
    </script>
</body>

</html>