<?php
include('../php/session_dispatcher.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriSakay | Dispatcher</title>
    <?php
    include '../dependencies/dependencies.php';
    ?>
    <style>
        p{
    color: white;
}

    .booking-info p {
        margin: 0;
    }

    </style>
    <link rel="stylesheet" href="../css/booking.css">
</head>

<body>
    <?php
    include('../php/navbar_dispatcher.php');
    ?>
    <div id="booking-data">
        <?php include('bookingdata.php'); ?>
    </div>
</body>

</html>