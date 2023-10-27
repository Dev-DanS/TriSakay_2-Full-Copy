var greenMarkerIcon = L.icon({
  iconUrl:
    "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png",
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
});

var pickupPoint;
if (pickupPoint) {
  var currentLatLng = pickupPoint.getLatLng();
  console.log("Latitude: ", currentLatLng.lat);
  console.log("Longitude: ", currentLatLng.lng);
}

function handleLocationError(error) {
  switch (error.code) {
    case error.PERMISSION_DENIED:
      alert("User denied the request for Geolocation. " + error.message);
      break;
    case error.POSITION_UNAVAILABLE:
      alert("Location information is unavailable. " + error.message);
      break;
    case error.TIMEOUT:
      alert("The request to get user location timed out. " + error.message);
      break;
    case error.UNKNOWN_ERROR:
      alert("An unknown error occurred. " + error.message);
      break;
  }
}

if ("geolocation" in navigator) {
  navigator.geolocation.getCurrentPosition(
    (position) => {
      var latlng = new L.LatLng(
        position.coords.latitude,
        position.coords.longitude
      );

      if (!pickupPoint) {
        pickupPoint = L.marker(latlng, { icon: greenMarkerIcon })
          .addTo(map)
          .bindPopup("You are here")
          .openPopup();
        map.setView(latlng, 15);
      } else {
        pickupPoint.setLatLng(latlng);
      }

      document.getElementById("pickup-address").textContent =
        "Locating your address...";

      axios
        .get(
          `https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}`
        )
        .then((response) => {
          var address = response.data.display_name;
          var addressWords = address.split(",");
          addressWords.splice(-4);
          addressWords = addressWords.filter(
            (word) => word.trim() !== "Doña Enriquieta Subdivision"
          );
          var shortenedAddress = addressWords.join(",");
          document.getElementById("pickup-address").textContent =
            "Pickup to: " + shortenedAddress;
        })
        .catch((error) => {
          console.error(error);
          document.getElementById("pickup-address").textContent =
            "Unable to locate your address.";
        });
    },
    handleLocationError,
    { enableHighAccuracy: true }
  );
} else {
  alert("Geolocation is not supported by your browser.");
}
