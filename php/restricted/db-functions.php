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

function getCustomerID($accountID) {
    GLOBAL $conn;
    $query = "SELECT      c.CustomerID
              FROM        Customer AS c
              WHERE       c.AccountID = :accountID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":accountID", $accountID);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return empty($result) ? false : $result[0]["CustomerID"];
}

function getEmployeeID($accountID) {
    GLOBAL $conn;
    $query = "SELECT      e.EmployeeID
              FROM        Employee AS e
              WHERE       e.AccountID = :accountID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":accountID", $accountID);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return empty($result) ? false : $result[0]["EmployeeID"];
}

function getFullName($accountID) {
    GLOBAL $conn;
    $query = "SELECT      c.FirstName, c.LastName
              FROM        Customer AS c
              WHERE       c.AccountID = :accountID
                  UNION
              SELECT      e.FirstName, e.LastName
              FROM        Employee AS e
              WHERE       e.AccountID = :accountID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":accountID", $accountID);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return empty($result) ? false : $result[0]["FirstName"] . " " . $result[0]["LastName"];
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

/******************************* TICKETS *******************************/
function closeTicket($ticketID, $dateModified = null) {
    GLOBAL $conn;
    if (is_null($dateModified)) { $dateModified = date("Y-m-d H:i:s"); }

    $query = "UPDATE      Ticket as t
              SET         t.TicketStatus = 'Closed', t.DateModified = :dateModified
              WHERE       t.TicketID = :ticketID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":ticketID", $ticketID);
    $stmt->bindParam(":dateModified", $dateModified);
    $stmt->execute();

    return $dateModified;
}

function getAccountTickets($accountID) {
    GLOBAL $conn;
    $query = "SELECT      t.TicketID, t.TicketSubject, t.DateCreated, t.DateModified, t.TicketStatus
              FROM        Ticket AS t
              WHERE       t.AccountID = :accountID
              ORDER BY    t.DateModified DESC;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":accountID", $accountID);
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);


    if (!empty($tickets)) {
        $query = "SELECT      t_m.TicketID, t_m.MessageSender, t_m.MessageText, t_m.MessageTime
                  FROM        TicketMessage AS t_m
                  WHERE       t_m.TicketID IN (
                      SELECT      t.TicketID
                      FROM        Ticket AS t
                      WHERE       t.AccountID = :accountID
                  )
                  ORDER BY    t_m.MessageTime DESC;";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":accountID", $accountID);
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tickets as $idx => $t) {
            $tickets[$idx]["Messages"] = array_filter($messages, function($var) use($t) { return ($var["TicketID"] == $t["TicketID"]); });
        }
    }

    return empty($tickets) ? [] : $tickets;
}

function getAllTickets() {
    GLOBAL $conn;
    $query = "SELECT      t.TicketID, t.TicketSubject, t.DateCreated, t.DateModified, t.TicketStatus
              FROM        Ticket AS t
              ORDER BY    t.TicketStatus DESC, t.DateModified DESC
              LIMIT       500;";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($tickets)) {
        $query = "SELECT      t_m.TicketID, t_m.MessageSender, t_m.MessageText, t_m.MessageTime
                  FROM        TicketMessage AS t_m INNER JOIN (
                                  SELECT      t.TicketID
                                  FROM        Ticket AS t
                                  ORDER BY    t.DateModified DESC
                                  LIMIT       500
                              ) AS t
                                  ON t_m.TicketID = t.TicketID
                  ORDER BY    t_m.MessageTime DESC;";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":accountID", $accountID);
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tickets as $idx => $t) {
            $tickets[$idx]["Messages"] = array_filter($messages, function($var) use($t) { return ($var["TicketID"] == $t["TicketID"]); });
        }
    }

    return empty($tickets) ? [] : $tickets;
}

function replyToTicket($accountID, $ticketID, $messageText, $messageTime = null) {
    GLOBAL $conn;
    if (is_null($messageTime)) { $messageTime = date("Y-m-d H:i:s"); }

    $messageSender = getFullName($accountID);

    $conn->beginTransaction();

    $query = "INSERT INTO TicketMessage (MessageSender, MessageText, MessageTime, TicketID)
                VALUES (:messageSender, :messageText, :messageTime, :ticketID);";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":messageSender", $messageSender);
    $stmt->bindParam(":messageText", $messageText);
    $stmt->bindParam(":messageTime", $messageTime);
    $stmt->bindParam(":ticketID", $ticketID);
    $stmt->execute();

    $query = "UPDATE      Ticket as t
              SET         t.DateModified = :messageTime
              WHERE       t.TicketID = :ticketID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":messageTime", $messageTime);
    $stmt->bindParam(":ticketID", $ticketID);
    $stmt->execute();

    $conn->commit();

    return ["DateModified" => $messageTime, "Message" => ["TicketID" => $ticketID, "MessageSender" => $messageSender, "MessageText" => $messageText, "MessageTime" => $messageTime]];
}

function uploadTicket($accountID, $ticketSubject, $messageText, $dateCreated = null, $dateModified = null) {
    GLOBAL $conn;
    if (is_null($dateCreated)) { $dateCreated = date("Y-m-d H:i:s"); }
    if (is_null($dateModified)) { $dateModified = date("Y-m-d H:i:s"); }

    $conn->beginTransaction();

    $query = "INSERT INTO Ticket (TicketSubject, DateCreated, DateModified, AccountID)
                  VALUES (:ticketSubject, :dateCreated, :dateModified, :accountID);";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":ticketSubject", $ticketSubject);
    $stmt->bindParam(":dateCreated", $dateCreated);
    $stmt->bindParam(":dateModified", $dateModified);
    $stmt->bindParam(":accountID", $accountID);
    $stmt->execute();

    $ticketID = $conn->lastInsertId();
    $messageSender = getFullName($accountID);

    $query = "INSERT INTO TicketMessage (MessageSender, MessageText, MessageTime, TicketID)
                  VALUES (:messageSender, :messageText, :dateCreated, :ticketID);";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":messageSender", $messageSender);
    $stmt->bindParam(":messageText", $messageText);
    $stmt->bindParam(":dateCreated", $dateCreated);
    $stmt->bindParam(":ticketID", $ticketID);
    $stmt->execute();

    $conn->commit();

    return $ticketID;
}

?>