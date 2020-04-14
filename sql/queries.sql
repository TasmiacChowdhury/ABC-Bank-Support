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
INSERT INTO Account (Username, PasswordHash)
    VALUES (:username, :passwordHash);

INSERT INTO Customer (FirstName, LastName, Email, RoutingNumber, AccountNumber, DateCreated, AccountID)
    VALUES (:firstName, :lastName, :email, :routingNumber, :accountNumber, :dateCreated, :accountID);

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
/* getAllTickets($accountID) */
SELECT      t.TicketID, t.TicketSubject, t.DateCreated, t.DateModified, t.TicketStatus
FROM        Ticket AS t
WHERE       t.AccountID = :accountID
ORDER BY    t.DateModified DESC;

SELECT      t_m.TicketID, t_m.MessageText
FROM        TicketMessage AS t_m
WHERE       t_m.TicketID IN (
    SELECT      t.TicketID
    FROM        Ticket AS t
    WHERE       t.AccountID = :accountID
)
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









