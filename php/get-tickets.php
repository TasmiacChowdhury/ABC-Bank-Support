<?php
require_once("restricted/db-functions.php");
include_once("restricted/logging.php");

try {
    if (isset($_SESSION["accid"]) && isset($_SESSION["cid"])) {
        $tickets = getAllTickets($_SESSION["accid"]);
        echo json_encode(["Success" => true, "Tickets" => $tickets]);
    } else {
        header("Location: ../login.php");
        exit;
    }
} catch(PDOException $e) {
    logToFile("Error: " . $e->getMessage(), "e");
	echo json_encode(["Success" => false, "Message" => "Error logged to file"]);
}

$conn = null;
?>