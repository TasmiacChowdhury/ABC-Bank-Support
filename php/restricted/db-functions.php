<?php
require_once("db-connect.php");
include_once("logging.php");

/******************************* USERS / ACCOUNTS *******************************/
function getAccountType($accountID) {
    GLOBAL $conn;
    $query = "SELECT      a.AccountType
              FROM        Account AS a
              WHERE       a.AccountID = :accountID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":accountID", $accountID);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return empty($result) ? false : $result[0]["AccountType"];
}

function getLoginInfo($username) {
    GLOBAL $conn;
    $query = "SELECT      a.AccountID, a.PasswordHash
              FROM        Account AS a
              WHERE       a.Username = :username;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return empty($result) ? false : $result[0];
}

function getPass($accountID) {
    GLOBAL $conn;
    $query = "SELECT      a.PasswordHash
              FROM        Account AS a
              WHERE       a.AccountID = :accountID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":accountID", $accountID);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return empty($result) ? false : $result[0]["PasswordHash"];
}

function getUser($username) {
    GLOBAL $conn;
    $query = "SELECT      a.AccountID
              FROM        Account AS a
              WHERE       a.Username = :username;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return empty($result) ? false : $result[0]["AccountID"];
}

function getUserInfo($accountID, $type) {
    GLOBAL $conn;
    $query = ($type == "c") ?
        "SELECT      a.Username, c.FirstName, c.LastName, c.Email, c.RoutingNumber, c.AccountNumber, c.Verified, c.Active, a.DateCreated
         FROM        Account AS a INNER JOIN Customer AS c
                         ON a.AccountID = c.AccountID
         WHERE       c.AccountID = :accountID;"
    :   "SELECT      a.Username, e.FirstName, e.LastName
         FROM        Account AS a INNER JOIN Employee AS e
                         ON a.AccountID = e.AccountID
         WHERE       e.AccountID = :accountID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":accountID", $accountID);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return empty($result) ? false : $result[0];
}

function register($username, $password, $firstName, $lastName, $email, $routingNumber, $accountNumber) {
    GLOBAL $conn;
    $date = date('Y-m-d H:i:s');
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $conn->beginTransaction();

    $stmt = "INSERT INTO Account (Username, PasswordHash, DateCreated)
                VALUES (:username, :passwordHash, :dateCreated);";
    $query = $conn->prepare($stmt);
    $query->bindParam(":username", $username);
    $query->bindParam(":passwordHash", $passwordHash);
    $query->bindParam(":dateCreated", $date);
    $query->execute();

    $accountID = $conn->lastInsertID();

    $stmt = "INSERT INTO Customer (FirstName, LastName, Email, RoutingNumber, AccountNumber, AccountID)
                VALUES (:firstName, :lastName, :email, :routingNumber, :accountNumber, :accountID);";
    $query = $conn->prepare($stmt);
    $query->bindParam(":firstName", $firstName);
    $query->bindParam(":lastName", $lastName);
    $query->bindParam(":email", $email);
    $query->bindParam(":routingNumber", $routingNumber);
    $query->bindParam(":accountNumber", $accountNumber);
    $query->bindParam(":accountID", $accountID);
    $query->execute();

    $conn->commit();

    return $accountID;
}

function updatePass($accountID, $passwordHash) {
    GLOBAL $conn;
    $query = "UPDATE      Account AS a
              SET         a.PasswordHash = :passwordHash
              WHERE       a.AccountID = :accountID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":accountID", $accountID);
    $stmt->bindParam(":passwordHash", $passwordHash);
    $stmt->execute();
}

/******************************* AUTHENTICATION TOKENS *******************************/
function createToken($accountID, $days) {
    GLOBAL $conn;
    $selector = base64_encode(random_bytes(15));
    $validator = base64_encode(random_bytes(33));
    $validatorHash = hash("sha256", $validator);
    $expiryDate = date("Y-m-d H:i:s", time() + (86400 * $days));

    $query = "INSERT INTO Token (Selector, ValidatorHash, ExpiryDate, AccountID)
                VALUES (:selector, :validatorHash, :expiryDate, :accountID);";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":selector", $selector);
    $stmt->bindParam(":validatorHash", $validatorHash);
    $stmt->bindParam(":expiryDate", $expiryDate);
    $stmt->bindParam(":accountID", $accountID);
    $stmt->execute();

    return $selector . ":" . $validator;
}

function deleteToken($selector) {
    GLOBAL $conn;
    if (strlen($selector) !== 20) {
        logToFile("Invalid selector $selector", "e");
        return false;
    }

    $query = "DELETE
              FROM        Token
              WHERE       Selector = :selector;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":selector", $selector);
    $stmt->execute();

    return true;
}

function validateToken($token) {
    GLOBAL $conn;
    if (strpos($token, ":") === false) {
        logToFile("Failed to find ':' in token $token", "e");
        return false;
    }

    list($selector, $validator) = explode(":", $token);

    if (strlen($selector) !== 20 || strlen($validator) !== 44) {
        logToFile("Invalid length of selector [$selector] or validator [$validator]", "e");
        return false;
    }

    $validatorHash = hash("sha256", $validator);

    $query = "SELECT      t.ValidatorHash, t.ExpiryDate, t.AccountID
              FROM        Token AS t
              WHERE       t.Selector = :selector;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":selector", $selector);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($result) || !hash_equals($result[0]["ValidatorHash"], $validatorHash)) {
        logToFile("Selector lookup failed or validator does not match", "e");
        return false;
    } else if (time() - strtotime($result[0]["ExpiryDate"]) > 0) {
        $deleteResult = deleteToken($conn, $selector);
        if ($deleteResult) { logToFile("Token [$token] has expired and been deleted"); }
        else { logToFile("Failed to delete token [$token]", "e"); }
        return false;
    }

    return $result[0]["AccountID"];
}

?>