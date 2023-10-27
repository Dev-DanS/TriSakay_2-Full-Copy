var redMarkerIcon = L.icon({
  iconUrl:
    "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png",
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
});

function addDropoffPoint(e) {
  let isInPolygon = false;
  polygonsLayer.eachLayer(function (layer) {
    if (isPointInPolygon(e.latlng, layer)) {
      isInPolygon = true;
      selectedToda = layer.toda; // Assign the TODA of the clicked polygon to the global variable
      return false;
    }
  });

  if (isInPolygon) {
    // If a drop-off marker already exists, remove it before adding a new one
    if (dropoffPoint) {
      map.removeLayer(dropoffPoint);
    }
    // Adding a drop-off marker using the red icon
    dropoffPoint = L.marker(e.latlng, { icon: redMarkerIcon }).addTo(map);
    dropoffPoint.bindPopup("Drop-off Point").openPopup();

    // Fetching address for drop-off point
    fetchAddressForLatLng(e.latlng.lat, e.latlng.lng);
    getShortestPath(pickupPoint.getLatLng(), e.latlng);
  } else {
    alert("Please click inside an available drop-off location.");
  }
}

function fetchAddressForLatLng(lat, lng) {
  axios
    .get(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`
    )
    .then((response) => {
      var address = response.data.display_name;
      var addressWords = address.split(",");
      addressWords.splice(-4); // Retaining this line to remove last 4 words
      addressWords = addressWords.filter(
        (word) => word.trim() !== "Doña Enriquieta Subdivision"
      );
      var shortenedAddress = addressWords.join(",");
      document.getElementById("dropoff-address").textContent =
        "Drop-off to: " + shortenedAddress;
    })
    .catch((error) => {
      console.error(error);
      document.getElementById("dropoff-address").textContent =
        "Unable to locate the drop-off address.";
    });
}
