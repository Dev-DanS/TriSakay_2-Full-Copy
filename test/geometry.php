<!DOCTYPE html>
<html>

<head>
    <title>Leaflet Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</head>

<body>
    <h1 id="todaInfo">Click a shape to see its Toda</h1>
    <div id="map" style="height: 400px;"></div>
    <button id="confirmPickup" disabled>Confirm Pickup Point</button>
    <button id="confirmDropoff" disabled>Confirm Drop Off Point</button>

    <script>
        let terminalMarker = null;
        let clickedOnPolygon = false;
        let pickupMarker = null;
        let dropoffMarker = null;
        let currentPolygon = null;

        const map = L.map('map').setView([14.9529, 120.8995], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        const confirmPickupBtn = document.getElementById('confirmPickup');
        const confirmDropoffBtn = document.getElementById('confirmDropoff');

        fetch('fetch-data.php')
            .then(response => response.json())
            .then(data => {
                data.forEach(entry => {
                    const border = entry.borders;
                    const toda = entry.toda;
                    if (border.type === 'polygon') {
                        const polygon = L.polygon(border.latlngs[0]).addTo(map);
                        polygon.on('click', () => {
                            clickedOnPolygon = true;
                            currentPolygon = polygon;
                            document.getElementById('todaInfo').innerText = `Toda: ${toda}`;
                            fetchTerminal(toda);
                        });
                    }
                });
            });

        map.on('click', () => {
            if (!clickedOnPolygon) {
                if (terminalMarker) {
                    map.removeLayer(terminalMarker);
                }
                document.getElementById('todaInfo').innerText = 'Click a shape to see its Toda';
            }
            clickedOnPolygon = false;
        });

        map.on('dblclick', (e) => {
            if (currentPolygon && currentPolygon.getBounds().contains(e.latlng)) {
                if (!pickupMarker) {
                    pickupMarker = L.marker(e.latlng).addTo(map);
                    pickupMarker.bindPopup("Pickup Point").openPopup();
                    confirmPickupBtn.disabled = false;
                } else if (pickupMarker && !dropoffMarker) {
                    dropoffMarker = L.marker(e.latlng).addTo(map);
                    dropoffMarker.bindPopup("Drop Off Point").openPopup();
                    confirmDropoffBtn.disabled = false;
                }
            }
        });

        confirmPickupBtn.addEventListener('click', () => {
            if (pickupMarker) {
                pickupMarker.dragging.disable();
                confirmPickupBtn.disabled = true;
            }
        });

        confirmDropoffBtn.addEventListener('click', () => {
            if (dropoffMarker) {
                dropoffMarker.dragging.disable();
                confirmDropoffBtn.disabled = true;
            }
        });

        function fetchTerminal(toda) {
            fetch(`fetch-terminals.php?toda=${toda}`)
                .then(response => response.json())
                .then(data => {
                    if (terminalMarker) {
                        map.removeLayer(terminalMarker);
                    }
                    const coords = data.terminal.split(',').map(Number);
                    terminalMarker = L.marker(coords).addTo(map);
                    terminalMarker.bindPopup(`${toda} Terminal`).openPopup();
                });
        }
    </script>
</body>

</html>