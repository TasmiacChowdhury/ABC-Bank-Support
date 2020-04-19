/******************************* REGISTER EMPLOYEES *******************************/
/*  • PasswordHash should be generated using any updated PHP password-hashing tool using
    the PASSWORD_DEFAULT (currently equivalent to PASSWORD_BCRYPT in PHP 7.2) algorithim
    • Database management GUI, such as HeidiSQL or MySQLWorkbench, should be used to make
    this process simpler in terms of getting the AccountID and entering values. The queries
    below are only for reference purposes of what values need to be entered
    • The EmployeeID is not an AUTO_INCREMENT field as it is the same ID used in the bank's
    main database (for the reasons of having all of the employee information stored there
    already and not duplicating it here needlessly). Therefore, it needs to be manually entered
*/
INSERT INTO Account (Username, PasswordHash, DateCreated)
    VALUES (:username, :passwordHash, NOW());

INSERT INTO Employee (EmployeeID, FirstName, LastName, AccountID)
    VALUES (:employeeID, :firstName, :lastName, :accountID);

/******************************* USERS / ACCOUNTS *******************************/
/* getAccountType($accountID) */
SELECT      a.AccountType
FROM        Account AS a
WHERE       a.AccountID = :accountID;

/* getCustomerID($accountID) */
SELECT      c.CustomerID
FROM        Customer AS c
WHERE       c.AccountID = :accountID;

/* getEmployeeID($accountID) */
SELECT      e.EmployeeID
FROM        Employee AS e
WHERE       e.AccountID = :accountID;

/* getFullName($accountID, $type) */
SELECT      c.FirstName, c.LastName
FROM        Customer AS c
WHERE       c.AccountID = :accountID;

SELECT      e.FirstName, e.LastName
FROM        Employee AS e
WHERE       e.AccountID = :accountID;

/* getLoginInfo($username) */
SELECT      a.AccountID, a.PasswordHash
FROM        Account AS a
WHERE       a.Username = :username;

/* getPass($accountID) */
SELECT      a.PasswordHash
FROM        Account AS a
WHERE       a.AccountID = :accountID;

/* getUser($username) */
SELECT      a.AccountID
FROM        Account AS a
WHERE       a.Username = :username;

/* getUserInfo($accountID, $type) */
SELECT      a.Username, c.FirstName, c.LastName, c.Email, c.RoutingNumber, c.AccountNumber, c.Verified, c.Active, c.DateCreated
FROM        Account AS a INNER JOIN Customer AS c
                ON a.AccountID = c.AccountID
WHERE       c.AccountID = :accountID;

                /* OR */
SELECT      a.Username, e.FirstName, e.LastName
FROM        Account AS a INNER JOIN Employee AS e
                ON a.AccountID = e.AccountID
WHERE       e.AccountID = :accountID;

/* register($email, $username, $password) */
INSERT INTO Account (Username, PasswordHash, DateCreated)
    VALUES (:username, :passwordHash, :dateCreated);

INSERT INTO Customer (FirstName, LastName, Email, RoutingNumber, AccountNumber, AccountID)
    VALUES (:firstName, :lastName, :email, :routingNumber, :accountNumber, :accountID);

/* updatePass($accountID, $passwordHash) */
UPDATE      Account AS a
SET         a.PasswordHash = :passwordHash
WHERE       a.AccountID = :accountID;

/******************************* AUTHENTICATION TOKENS *******************************/
/* createToken($accountID, $days) */
INSERT INTO Token (Selector, ValidatorHash, ExpiryDate, AccountID)
    VALUES (:selector, :validatorHash, :expiryDate, :accountID);

/* deleteToken($selector) */
DELETE
FROM        Token
WHERE       Selector = :selector;

/* validateToken($token) */
SELECT      t.ValidatorHash, t.ExpiryDate, t.AccountID
FROM        Token AS t
WHERE       t.Selector = :selector;

/******************************* TICKETS *******************************/
/* getAccountTickets($accountID) */
SELECT      t.TicketID, t.TicketSubject, t.DateCreated, t.DateModified, t.TicketStatus
FROM        Ticket AS t
WHERE       t.AccountID = :accountID
ORDER BY    t.DateModified DESC;

SELECT      t_m.TicketID, t_m.MessageSender, t_m.MessageText, t_m.MessageTime
FROM        TicketMessage AS t_m
WHERE       t_m.TicketID IN (
                SELECT      t.TicketID
                FROM        Ticket AS t
                WHERE       t.AccountID = :accountID
            )
ORDER BY    t_m.TicketMessageID DESC;

/* getAllTickets() */
SELECT      t.TicketID, t.TicketSubject, t.DateCreated, t.DateModified, t.TicketStatus
FROM        Ticket AS t
ORDER BY    t.DateModified DESC
LIMIT       500;

SELECT      t_m.TicketID, t_m.MessageSender, t_m.MessageText, t_m.MessageTime
FROM        TicketMessage AS t_m INNER JOIN (
                SELECT      t.TicketID
                FROM        Ticket AS t
                ORDER BY    t.DateModified DESC
                LIMIT       500
            ) AS t
                ON t_m.TicketID = t.TicketID
ORDER BY    t_m.TicketMessageID DESC;

/* uploadTicket($accountID, $subject, $messageText, $dateCreated, $dateModified) */
INSERT INTO Ticket (TicketSubject, DateCreated, DateModified, AccountID)
    VALUES (:ticketSubject, :dateCreated, :dateModified, :accountID);

INSERT INTO TicketMessage (MessageSender, MessageText, MessageTime, TicketID)
    VALUES (:messageSender, :messageText, :dateCreated, :ticketID);

/*
INSERT INTO TicketLog (ActionTaken, ActionTime, TicketID, AccountID)
    VALUES (:actionTaken, :dateCreated, :ticketID, :accountID);
*/









