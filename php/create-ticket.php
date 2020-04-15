<?php
require_once("restricted/db-functions.php");
include_once("restricted/logging.php");

try {
    if (isset($_SESSION["accid"])) {
        $accountID = $_SESSION["accid"];
        $subject = $_POST["subject"];
        $messageText = $_POST["messageText"];

        if (empty($subject) || empty($messageText)) {
            echo json_encode(["Success" => false, "Message" => "Subject and Message fields are required"]);
        } else if (strlen($subject) > 255) {
            echo json_encode(["Success" => false, "Message" => "Subject cannot be more than 255 characters"]);
        } else if (strlen($messageText) > 65535) {
            echo json_encode(["Success" => false, "Message" => "Message cannot be more than 65,535 characters"]);
        } else {
            $date = date("Y-m-d H:i:s");
            $ticketID = uploadTicket($accountID, $subject, $messageText, $date, $date);

            echo json_encode(["Success" => true, "Ticket" => ["TicketID" => $ticketID, "TicketSubject" => $subject, "DateCreated" => $date, "DateModified" => $date,
                "TicketStatus" => "Open", "Messages" => [["MessageSender" => getFullName($accountID, "customer"), "MessageTime" => $date, "MessageText" => $messageText]]]]);
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