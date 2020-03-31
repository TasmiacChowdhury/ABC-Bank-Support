<?php
require_once("restricted/db-functions.php");
include_once("restricted/logging.php");

try {
    if (isset($_SESSION["accid"])) {
        $accType = $_SESSION["acctype"];
        $info = getUserInfo($_SESSION["accid"], $accType);
        $info["AccountType"] = $accType;
        echo json_encode(["Success" => true, "Info" => $info]);
    } else {
        echo json_encode(["Success" => false, "Message" => "User not signed in"]);
    }
} catch(PDOException $e) {
    logToFile("Error: " . $e->getMessage(), "e");
	echo json_encode(["Success" => false, "Message" => "Error logged to file"]);
}

$conn = null;
?>