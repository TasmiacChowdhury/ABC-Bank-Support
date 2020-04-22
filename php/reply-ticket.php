<?php
require_once("restricted/db-functions.php");
include_once("restricted/logging.php");

try {
    if (isset($_SESSION["accid"])) {
        $accountID = $_SESSION["accid"];
        $ticketID = $_POST["ticketID"];
        $messageText = $_POST["messageText"];

        if (empty($messageText) && $_FILES["files"]["error"] == 4) {
            echo json_encode(["Success" => false, "Message" => "Either a message or attachment are required"]);
        } else if (strlen($messageText) > 65535) {
            echo json_encode(["Success" => false, "Message" => "Message cannot be more than 65,535 characters"]);
        } else {
            $response = replyToTicket($accountID, $ticketID, $messageText);
            echo json_encode(["Success" => true, "DateModified" => $response["DateModified"], "Message" => $response["Message"]]);
        }
    } else {
        echo json_encode(["Success" => false, "Message" => "User not signed in"]);
    }
} catch(PDOException $e) {
    logToFile("Error: " . $e->getMessage(), "e");
	echo json_encode(["Success" => false, "Message" => "Error logged to file"]);
}

$conn = null;
?>