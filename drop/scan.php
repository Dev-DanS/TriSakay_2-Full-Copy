<?php
include('../php/session_commuter.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan | Commuter</title>
    <?php
    include '../dependencies/dependencies.php';
    ?>
    <?php
    $imagePath = "../img/Logo_Nobg.png";
    ?>
    <link rel="icon" href="<?php echo $imagePath; ?>" type="image/png" />
    <script src="https://unpkg.com/@zxing/library@0.19.1"></script>
    <link rel="stylesheet" href="../css/scan.css">
</head>
<body>
    <?php
    include('../php/navbar_commuter.php');
    ?>
    <video id="qr-video" width="100%" height="50%"></video>
    <div id="result"></div>

    <script>
    // Function to handle the QR code scanning
    const videoElement = document.getElementById('qr-video');
    const resultElement = document.getElementById('result');

    const codeReader = new ZXing.BrowserQRCodeReader();

    codeReader
        .decodeFromVideoDevice(undefined, 'qr-video', (result, err) => {
            if (result) {
                resultElement.innerHTML = `QR Code Result: ${result.text}`;
                
                // Add the code to redirect and compare the QR result here
                // You can add it directly below this comment

                // Example: Redirect to the PHP script with the QR result as a parameter
                window.location.href = 'scanback.php?qr_result=' + result.text;
            }
            if (err && !(err instanceof ZXing.NotFoundException)) {
                console.error('QR Code scan error:', err);
                resultElement.innerHTML = 'Error scanning QR code.';
            }
        })
        .catch(error => {
            console.error('Error accessing the camera:', error);
            resultElement.innerHTML = 'Error accessing the camera.';
        });
</script>

    
</body>
</html>