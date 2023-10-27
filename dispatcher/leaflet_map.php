<!DOCTYPE html>
<html>

<head>
    <title>Booking | Dispatcher</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>

<body>
    <div id="map" style="width: 100%; height: 400px;"></div>
    <h1 id="pickupInfo"></h1>
    <h1 id="dropoffInfo"></h1>
    <h1 id="terminalInfo">Terminal:</h1>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const bookingId = urlParams.get("bookingid");

        const map = L.map('map', {
            zoomControl: false,
            doubleClickZoom: false
        }).setView([0, 0], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var greenMarkerIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

        var redMarkerIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

        fetch('get_coordinates.php?bookingid=' + bookingId)
            .then(response => response.json())
            .then(data => {
                const pickupPoint = data.pickupPoint;
                const dropoffPoint = data.dropoffPoint;
                const terminalPoint = data.terminal;

                // Update h1 elements with the coordinates
                document.getElementById("pickupInfo").textContent += `  ${pickupPoint}`;
                document.getElementById("dropoffInfo").textContent += `  ${dropoffPoint}`;
                document.getElementById("terminalInfo").textContent += `  ${terminalPoint.latlng.lat},${terminalPoint.latlng.lng}`;

                L.marker(pickupPoint).addTo(map)
                    .bindPopup('Pickup Point')
                    .setIcon(greenMarkerIcon);

                L.marker(dropoffPoint).addTo(map)
                    .bindPopup('Drop-off Point')
                    .setIcon(redMarkerIcon);

                L.marker(terminalPoint.latlng).addTo(map)
                    .bindPopup('Terminal')

                map.setView(terminalPoint.latlng, 15);

                getShortestRoute(pickupPoint.latlng, dropoffPoint.latlng);

            })
            .catch(error => {
                console.error('Error fetching coordinates:', error);
            });

            
            
    </script>
</body>

</html>