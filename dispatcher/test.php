<!DOCTYPE html>
<html>

<head>
    <title>Shortest Route Example</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        #map {
            height: 500px;
        }
    </style>
</head>

<body>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const bookingId = urlParams.get("bookingid");

        // Fetch pickup and dropoff coordinates from the PHP script.
        fetch(`testend.php?bookingid=${bookingId}`)
            .then(response => response.json())
            .then(coordinates => {
                const pickupPoint = coordinates.pickup;
                const dropoffPoint = coordinates.dropoff;

                var map = L.map('map').setView([14.9526, 120.8979], 15);


        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        

        var Terminal = [14.952606079606278, 120.89787847559734];
        // var pickupPoint = [14.954283534502583, 120.90080909502916];
        // var dropoffPoint = [14.953906700520372, 120.89089393615724];

        // Add markers for the points
        L.marker(Terminal).addTo(map).bindPopup("Terminal");
        L.marker(pickupPoint).addTo(map).bindPopup("Pickup");
        L.marker(dropoffPoint).addTo(map).bindPopup("Dropoff");

        // Make a request to OSRM for the route
        var osrmRouteURL = `https://router.project-osrm.org/route/v1/driving/${Terminal[1]},${Terminal[0]};${pickupPoint[1]},${pickupPoint[0]};${dropoffPoint[1]},${dropoffPoint[0]}?overview=full&geometries=geojson`;

        fetch(osrmRouteURL)
            .then(response => response.json())
            .then(data => {
                var route = L.geoJSON(data.routes[0].geometry, {
                }).addTo(map);
                map.fitBounds(route.getBounds());
            })
            .catch(error => console.error(error));

            })
            .catch(error => console.error(error));
    </script>
</body>

</html>