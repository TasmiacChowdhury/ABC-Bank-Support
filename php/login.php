<?php
require_once("restricted/db-functions.php");
include_once("restricted/logging.php");

try {
    if (isset($_SESSION["accid"]) && !empty($_SESSION["accid"])) {
        echo json_encode(["Success" => true, "Message" => "User already logged in"]);
    } else if (isset($_COOKIE["authToken"]) && !empty($_COOKIE["authToken"])) {
        $accountID = validateToken($_COOKIE["authToken"]);
        if ($accountID === false) {
            setcookie("authToken", "", 1); //delete cookie
            echo json_encode(["Success" => false, "Message" => "Invalid authentication token"]);
        } else {
            $_SESSION["acctype"] = getAccountType($accountID);
            $_SESSION["accid"] = $accountID;
            $_SESSION["username"] = $username;
            echo json_encode(["Success" => true, "Message" => "Login via token successful"]);
        }
    } else if (!empty($_POST) && isset($_POST["username"]) && isset($_POST["password"])) {
        $username = $_POST["username"];
        $password = $_POST["password"];
        if (empty($username) || empty($password)) {
            echo json_encode(["Success" => false, "Message" => "All fields are required"]);
        } else {
            $loginInfo = getLoginInfo($username);

            if (!empty($loginInfo)) {
                $accountID = $loginInfo["AccountID"];
                $passwordHash = $loginInfo["PasswordHash"];
                if (password_verify($password, $passwordHash)) {
                    if (isset($_POST["remember-me"])) { setcookie("authToken", createToken($accountID, 30), time() + (86400 * 30), "/", ""); } // , TRUE, TRUE);   --removed for local testing without https

                    $_SESSION["acctype"] = getAccountType($accountID);
                    $_SESSION["accid"] = $accountID;
                    $_SESSION["username"] = $username;

                    echo json_encode(["Success" => true, "Message" => "Login successful"]);
                } else {
                    echo json_encode(["Success" => false, "Message" => "Incorrect login credentials"]);
                }
            } else {
                echo json_encode(["Success" => false, "Message" => "Incorrect login credentials"]);
            }
        }
    } else {
        echo json_encode(["Success" => false, "Message" => "Invalid form information / no attempt made to login"]);
    }
} catch(PDOException $e) {
    logToFile("Error: " . $e->getMessage(), "e");
	echo json_encode(["Success" => false, "Message" => "Error logged to file"]);
}

$conn = null;
?>