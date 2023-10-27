<?php
require_once("dbconn.php");

$dayOrNight = $_GET['dayOrNight'];
$passengers = $_GET['passengers'];
$distance = $_GET['distance'];

if ($dayOrNight === "night") {
    $query = "SELECT BaseFare, PerKM, NightDiff, FarePerPassenger FROM farematrix WHERE status = 'active'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();

    $fare = ($distance - 2) * ($row['PerKM'] + $row['NightDiff']);
} else {
    $query = "SELECT BaseFare, PerKM, NightDiff, FarePerPassenger FROM farematrix WHERE status = 'active'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();

    $fare = ($distance - 2) * $row['PerKM'];
}

$baseFare = $row['BaseFare'];

if ($distance <= 2) {
    $fare = $baseFare + ($passengers > 2 ? $row['FarePerPassenger'] : 0);
} else {
    $fare = $baseFare + $fare + ($passengers > 2 ? $row['FarePerPassenger'] : 0);
}

echo $fare;
?>
