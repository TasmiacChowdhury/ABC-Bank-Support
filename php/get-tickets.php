<?php
require_once("restricted/db-functions.php");
include_once("restricted/logging.php");

try {
    if (!isset($_SESSION["accid"])) {
        header("Location: ../login.php");
        exit;
    }
    $accountID = $_SESSION["accid"];
    if (isset($_SESSION["cid"])) {
        $tickets = getAccountTickets($accountID);
        echo json_encode(["Success" => true, "Tickets" => $tickets]);
    } else if (isset($_SESSION["eid"])) {
        $tickets = getAllTickets($accountID);
        echo json_encode(["Success" => true, "Tickets" => $tickets]);
    }
} catch(PDOException $e) {
    logToFile("Error: " . $e->getMessage(), "e");
	echo json_encode(["Success" => false, "Message" => "Error logged to file"]);
}

$conn = null;
?>