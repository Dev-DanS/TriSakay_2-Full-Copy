<?php
require_once "db/dbconn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_email = $_POST["email"];
    $input_password = $_POST["password"];

    // Hash the input password using SHA-512
    $hashed_password = hash("sha512", $input_password);

    $stmt = $conn->prepare("SELECT commuterid, password, status FROM commuter WHERE email = ?");
    $stmt->bind_param("s", $input_email);
    $stmt->execute();
    $stmt->store_result();

    $stmt->bind_result($commuterid, $password, $status);
    $stmt->fetch();

    if ($stmt->num_rows == 1 && $hashed_password == $password) {
        if ($status !== null) {
            // Account is banned
            header("Location: index.php?error=banned");
            exit();
        }

        session_id($commuterid);
        session_start();

        $_SESSION["commuterid"] = $commuterid;
        $_SESSION["role"] = "commuter"; // Set role session for commuter

        header("Location: commuter/commuter.php");
        exit();
    } else {
        $stmt->close();

        // If not found in commuter table, check dispatcher table
        $stmt = $conn->prepare("SELECT dispatcherid, password, status FROM dispatcher WHERE email = ?");
        $stmt->bind_param("s", $input_email);
        $stmt->execute();
        $stmt->store_result();

        $stmt->bind_result($dispatcherid, $password, $status);
        $stmt->fetch();

        if ($stmt->num_rows == 1 && $hashed_password == $password) {
            if ($status !== null) {
                // Account is banned
                header("Location: index.php?error=banned");
                exit();
            }

            session_id($dispatcherid);
            session_start();

            $_SESSION["dispatcherid"] = $dispatcherid;
            $_SESSION["role"] = "dispatcher"; // Set role session for dispatcher

            header("Location: dispatcher/dispatcher.php");
            exit();
        } else {
            $stmt->close();

            header("Location: index.php?error=true");
            exit();
        }
    }
}

$conn->close();
?>