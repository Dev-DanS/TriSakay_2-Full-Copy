var passengerCount = 1; // default value

document
  .getElementById("passenger-dropdown")
  .addEventListener("click", function (e) {
    if (e.target && e.target.nodeName == "A") {
      passengerCount = e.target.getAttribute("data-value");
      document.getElementById("passenger-display").innerText = passengerCount;

      // Check if the dropoff point is already added, and if so, recalculate the fare
      if (dropoffPoint) {
        recalculateFare();
      }
    }
  });

function recalculateFare() {
  getShortestPath(pickupPoint.getLatLng(), dropoffPoint.getLatLng());
}
