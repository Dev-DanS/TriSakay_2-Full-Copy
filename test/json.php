<!DOCTYPE html>
<html>

<head>
    <title>Leaflet Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</head>

<body>

    <div id="map" style="height: 400px;"></div>
    <button id="save">Save Coordinates</button>

    <script>
        const map = L.map('map').setView([51.505, -0.09], 13);
        let marker;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        map.on('dblclick', function (event) {
            if (marker) {
                map.removeLayer(marker);
            }
            marker = L.marker(event.latlng).addTo(map);
        });

        document.getElementById('save').addEventListener('click', function () {
            if (marker) {
                const coordinates = marker.getLatLng();
                fetch('save_coordinates.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ lat: coordinates.lat, lng: coordinates.lng })
                })
                    .then(response => response.text())
                    .then(data => console.log(data))
                    .catch(error => console.error('Error:', error));
            }
        });

    </script>

</body>

</html>